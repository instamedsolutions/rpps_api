<?php

namespace App\Service;

use App\DataFixtures\LoadRPPS;
use App\Entity\City;
use App\Entity\RPPS;
use App\Entity\RPPSAddress;
use App\Entity\Specialty;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Random\RandomException;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Symfony\Component\String\u;

class RPPSService extends ImporterService
{
    private int $matchedCitiesCount = 0;
    private int $unmatchedCitiesCount = 0;

    private const int MAX_CANONICAL_CACHE_SIZE = 50000;

    // Hashmaps for specialties to avoid unnecessary DB queries
    private array $specialtyByName = [];  // The name field of the Specialty entity
    private array $specialtyByAltName = []; // Mapping of Instamed RPPS db specialties to our specialties
    private array $existingCanonicals = []; // Hashmap to store existing canonicals to avoid duplicate queries

    // Cache pour éviter les doublons d'adresses dans le même batch
    // Taille limitée au batch size pour optimiser la mémoire
    private array $addressCache = []; // Format: "rpps_id|md5_hash" => RPPSAddress

    public function __construct(
        protected readonly string $cps,
        protected readonly string $rpps,
        FileProcessor $fileProcessor,
        EntityManagerInterface $em,
    ) {
        parent::__construct(RPPS::class, $fileProcessor, $em);
        $this->initializeSpecialtyMaps();
    }

    // Initialize the hashmaps for specialties
    private function initializeSpecialtyMaps(): void
    {
        $specialties = $this->em->getRepository(Specialty::class)->findAll();

        foreach ($specialties as $specialty) {
            $this->specialtyByName[$specialty->getName()] = true; // Use `true` as a placeholder
        }

        $this->specialtyByAltName = SpecialtyMappingService::SPECIALTY_MAPPING;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function loadTestData(): void
    {
        $this->output->writeln('Deletion of existing data in progress');

        $ids = [
            '21234567890',
            '20987654321',
        ];
        for ($j = 1; $j <= 9; ++$j) {
            $ids[] = "1$j$j$j$j$j$j$j$j$j$j";
            $ids[] = "2$j$j$j$j$j$j$j$j$j$j";
        }

        $this->em->getConnection()->executeQuery(
            'DELETE FROM rpps WHERE id_rpps IN (:ids)',
            ['ids' => $ids],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        $this->output->writeln('Existing data successfully deleted');

        $fixture = new LoadRPPS();
        /* @phpstan-ignore-next-line */
        $fixture->importId = $this->getImportId();
        $fixture->load($this->em);

        $this->output->writeln('Test data successfully loaded');
    }

    /**
     * @throws Exception
     */
    public function importFile(OutputInterface $output, string $type, int $start = 0, int $limit = 0): bool
    {
        /** Handling File */
        $file = $this->fileProcessor->getFile($this->$type, $type, true, 'rpps' === $type ? 1 : 0);

        if ('rpps' === $type) {
            $options = ['delimiter' => '|', 'utf8' => true, 'headers' => true];
        } elseif ('cps' === $type) {
            $options = ['delimiter' => '|', 'utf8' => false, 'headers' => true];
        } else {
            throw new RuntimeException("Type $type not working");
        }
        // ===== DEV -(DELETE WHEN OK ON PROD) =====
        // Use a plain CSV file (not zip). Ensure the path exists INSIDE the container.
        // $file = '/var/www/html/var/rpps/duplicates_test_100k.csv';
        // $output->writeln("<info>[DEV] Using CSV (not zip): $file</info>");
        // if (!is_readable($file)) {$output->writeln("<error>[DEV] File is not found or unreadable: $file</error>");return false;}
        // ===== END DEV HARDCODE =====

        $process = $this->processFile($output, $file, $type, $options, $start, $limit);

        // unlink($file);

        $this->output->writeln('Total Matched Cities: ' . $this->matchedCitiesCount);
        $this->output->writeln('Total Unmatched Cities: ' . $this->unmatchedCitiesCount);

        if ('rpps' === $type) {
            // Purge addresses not touched in this run (not having current importId)
            $this->purgeStaleAddresses();
        }

        return $process;
    }

    protected function clearCache(): void
    {
        parent::clearCache();
        // Vider le cache d'adresses à chaque batch (tous les 50 éléments)
        $this->addressCache = [];
    }

    /**
     * @throws Exception
     */
    protected function processData(array $data, string $type): ?RPPS
    {
        return match ($type) {
            'cps' => $this->processCPS($data),
            'rpps' => $this->processRPPS($data),
            default => throw new Exception("Type $type is not supported yet"),
        };
    }

    protected function processCPS(array $data): ?RPPS
    {
        /** @var RPPS|null $rpps */
        $rpps = $this->repository->find($data[0]);

        if (null === $rpps) {
            return null;
        }

        $rpps->setCpsNumber($data[11]);

        return $rpps;
    }

    /**
     * @throws RandomException
     */
    protected function processRPPS(array $data): ?RPPS
    {
        $rpps = $this->entities[$data[1]] ?? $this->repository->find($data[1]);

        if ($rpps instanceof RPPS) {
            return $this->updateRppsFromRow($rpps, $data);
        }

        return $this->createRppsFromRow($data);
    }

    /**
     * Create a new RPPS from a CSV row.
     * - Fill the RPPS entity (title/firstName/lastName/specialty/...).
     * - Try to create the RPPSAddress if address data is present.
     *
     * @throws RandomException
     */
    private function createRppsFromRow(array $data): RPPS
    {
        $rpps = new RPPS();
        $rpps->setIdRpps($data[1]);

        // $data[3] : DR
        // $data[4] : Docteur
        // $data[5] : M
        // $data[6] : Monsieur
        // Title assignment based on priority: data[4] / data[3] / data[6] / data[5]
        $title = null;
        foreach ([$data[4], $data[3], $data[6], $data[5]] as $candidate) {
            if (!empty($candidate)) {
                $title = $candidate;
                break;
            }
        }

        // Map short titles to extended versions
        $titleMapping = [
            'M' => 'Monsieur',
            'DR' => 'Docteur',
            'MME' => 'Madame',
            'PR' => 'Professeur',
        ];

        $expandedTitle = $titleMapping[$title] ?? $title;

        $rpps->setTitle($expandedTitle);
        $rpps->setLastName($data[7]);
        $rpps->setFirstName($data[8]);

        $this->handleSpecialty($rpps, $data);

        $this->createRppsAddress($rpps, $data);

        // Contacts and numbers on RPPS remain updated
        $rpps->setPhoneNumber(str_replace(' ', '', (string) ($data[40] ?? '')));
        $rpps->setEmail($data[43] ?? null);
        $rpps->setFinessNumber($data[21] ?? null);

        // Set canonical only if it is not already set
        if (!$rpps->getId() || !$rpps->getCanonical()) {
            $canonical = $this->generateCanonical($rpps);
            $rpps->setCanonical($canonical);
        }

        $rpps->setImportId($this->getImportId());

        $this->entities[$rpps->getIdRpps()] = $rpps;

        $this->em->persist($rpps);
        // No flush here; batch flushing is handled by FileParserService

        return $rpps;
    }

    /**
     * Helper unique pour créer une RPPSAddress à partir d'une ligne CSV.
     * - Retourne null si aucune donnée d'adresse exploitable (rien fait).
     * - Retourne RPPSAddress si une adresse a été créée/mise à jour et persistée.
     */
    private function createRppsAddress(RPPS $rpps, array $data): ?RPPSAddress
    {
        // Build address parts from the CSV (do NOT set on RPPS; we work on RPPSAddress)
        $addressLine = trim(($data[28] ?? '') . ' ' . ($data[31] ?? '') . ' ' . ($data[32] ?? ''));
        $addressExt = $data[33] ?? null;
        $zipcode = $data[35] ?? null;
        $cityName = $data[37] ?? null;

        $hasAnyAddressData = ('' !== $addressLine)
            || (null !== $zipcode && '' !== trim((string) $zipcode))
            || (null !== $cityName && '' !== trim((string) $cityName));

        if (!$hasAnyAddressData) {
            return null;
        }

        // Compute MD5 (hex) for normalized address parts
        $md5Hex = $this->computeAddressMd5Hex($addressLine, $cityName, $zipcode);

        // Clé de cache unique : rpps_id + md5_hash
        $cacheKey = $rpps->getIdRpps() . '|' . $md5Hex;

        // Vérifier le cache en cours de batch
        if (isset($this->addressCache[$cacheKey])) {
            $addr = $this->addressCache[$cacheKey];
            $addr->setImportId($this->getImportId());

            return $addr;
        }

        // City resolution for the address
        $cityEntity = $this->findCityEntity($zipcode, $cityName);
        if ($cityEntity) {
            ++$this->matchedCitiesCount;
        } else {
            ++$this->unmatchedCitiesCount;
        }

        // Find an existing address by (rpps, md5)
        /** @var RPPSAddress|null $addr */
        $addr = $this->em->getRepository(RPPSAddress::class)->findOneBy([
            'rpps' => $rpps,
            'md5Address' => $md5Hex,
        ]);

        if ($addr) {
            // Address already exists, update it with new import_id and return
            $addr->setImportId($this->getImportId());
            $this->em->persist($addr);

            return $addr;
        }

        $addr = new RPPSAddress();
        $addr->setRpps($rpps);
        $addr->setMd5AddressHex($md5Hex);
        $addr->setAddress($addressLine);
        $addr->setAddressExtension($addressExt);
        $addr->setZipcode($zipcode);
        $addr->setCity($cityEntity);
        $addr->refreshOriginalAddress();
        $addr->setImportId($this->getImportId());

        // Cache la nouvelle adresse pour éviter les doublons dans le batch
        $this->addressCache[$cacheKey] = $addr;

        $this->em->persist($addr);

        return $addr;
    }

    /**
     * Update flow:
     * If there is no usable address: SKIP + LOG and return null.
     * Try to complete missing info on the original RPPS with non-empty values from the new row
     * (never overwrite existing data).
     *
     * @throws RandomException
     */
    private function updateRppsFromRow(RPPS $rpps, array $data): ?RPPS
    {
        // Address first: if none -> skip entire update with log
        $rppsAddress = $this->createRppsAddress($rpps, $data);
        if (!$rppsAddress) {
            $this->output->writeln(
                sprintf(
                    '<comment>[RPPS import] Skipping empty address for idRpps=%s | line=%s</comment>',
                    $data[1] ?? '',
                    implode('|', array_map(static fn ($v) => (string) $v, $data))
                )
            );

            return null;
        }

        // Complete (never overwrite) identity/title
        // Priority: [libellé exercice, code exercice, libellé civilité, code civilité]
        if (!$rpps->getTitle()) {
            $title = null;
            foreach ([$data[4] ?? null, $data[3] ?? null, $data[6] ?? null, $data[5] ?? null] as $candidate) {
                if (!empty($candidate)) {
                    $title = $candidate;
                    break;
                }
            }
            if ($title) {
                $map = ['M' => 'Monsieur', 'DR' => 'Docteur', 'MME' => 'Madame', 'PR' => 'Professeur'];
                $expanded = $map[$title] ?? $title;
                $rpps->setTitle($expanded);
            }
        }

        if (!empty($data[7]) && !$rpps->getLastName()) {
            $rpps->setLastName($data[7]);
        }
        if (!empty($data[8]) && !$rpps->getFirstName()) {
            $rpps->setFirstName($data[8]);
        }

        // Complete specialty only if none set (entity nor legacy)
        if (!$rpps->getSpecialtyEntity() && !$rpps->getSpecialty()) {
            $this->handleSpecialty($rpps, $data);
        }

        // Contacts: complete if missing only
        $newPhone = isset($data[40]) ? str_replace(' ', '', (string) $data[40]) : null;
        if (!empty($newPhone) && !$rpps->getPhoneNumber()) {
            $rpps->setPhoneNumber($newPhone);
        }

        $newEmail = $data[43] ?? null;
        if (!empty($newEmail) && !$rpps->getEmail()) {
            $rpps->setEmail($newEmail);
        }

        $newFiness = $data[21] ?? null;
        if (!empty($newFiness) && !$rpps->getFinessNumber()) {
            $rpps->setFinessNumber($newFiness);
        }

        // Canonical: set only if empty
        if (!$rpps->getCanonical()) {
            $canonical = $this->generateCanonical($rpps);
            $rpps->setCanonical($canonical);
        }

        // Keep import trace if desired
        $rpps->setImportId($this->getImportId());

        $this->entities[$rpps->getIdRpps()] = $rpps;
        $this->em->persist($rpps);

        return $rpps;
    }

    private function handleSpecialty(RPPS $rpps, array $data): void
    {
        // Determine which specialty field to use
        if ($data[16] && in_array($data[13], ['S', 'CEX'])) {
            $specialtyName = $data[16];
        } else {
            // Fallback to $data[10] if $data[16] is not valid
            $specialtyName = $data[10];
        }

        if ($specialtyName) {
            $specialtyEntity = $this->findSpecialtyEntity($specialtyName);
            if ($specialtyEntity) {
                $rpps->setSpecialtyEntity($specialtyEntity);
            } else {
                // Fallback previous flow
                $rpps->setSpecialty($specialtyName);
                // Log or handle cases where the specialty is not found
                $this->output->writeln('No specialty found for: ' . $specialtyName);
            }
        }
    }

    private function findSpecialtyEntity(string $specialtyName): ?Specialty
    {
        $specialtyName = trim($specialtyName);

        // Check for the exact match
        if (isset($this->specialtyByName[$specialtyName])) {
            // Fetch from DB to ensure we have the most up-to-date entity,
            // avoiding memory overhead of storing full entities in the hashmap.
            // If we keep assigning the same entity in batch processing,
            // form the hashmap value, somehow doctrine will not be happy.
            return $this->em->getRepository(Specialty::class)->findOneBy(['name' => $specialtyName]);
        }

        // Check for alternative name match using the static array
        if (isset($this->specialtyByAltName[$specialtyName])) {
            return $this->em->getRepository(Specialty::class)
                ->findOneBy(['name' => $this->specialtyByAltName[$specialtyName]]);
        }

        // Log or handle case when no match is found
        $this->output->writeln("<error>No specialty found for: $specialtyName</error>");

        return null;
    }

    private function findCityEntity(mixed $zipCode, mixed $cityName): ?City
    {
        if (empty($zipCode) && empty($cityName)) {
            return null;
        }

        if (!empty($zipCode)) {
            // Find by postal code
            $cities = $this->em->getRepository(City::class)->findBy(['postalCode' => $zipCode]);
        } else {
            // Find by city name (lowercase comparison)
            $cities = $this->em->getRepository(City::class)->createQueryBuilder('c')
                ->where('LOWER(c.name) = :cityName OR LOWER(c.altName) = :cityName')
                ->setParameter('cityName', strtolower($cityName))  // Lowercase the input city name
                ->getQuery()
                ->getResult();
        }

        if (empty($cities)) {
            return null;
        }

        if (1 === count($cities)) {
            // If only one city found, return it
            return $cities[0];
        }

        // Try to find a city matching the normalized name
        if ($cityName) {
            $normalizedCityName = u($cityName)
                ->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

            // Check for matching city name
            $matchingNameCities = array_filter($cities, static function ($city) use ($normalizedCityName) {
                $n2 = u($city->getName())
                    ->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

                return $n2 === $normalizedCityName;
            });

            if (1 === count($matchingNameCities)) {
                return array_pop($matchingNameCities);
            }

            // From the cities with the same name, is there a unique main city?
            $matchingNameMainCities = array_filter($matchingNameCities, static function ($city) {
                return $city->isMainCity();
            });

            if (1 === count($matchingNameMainCities)) {
                return array_pop($matchingNameMainCities);
            }

            // Check for matching subcity name
            $matchingSubCities = array_filter($matchingNameCities, static function ($city) use ($normalizedCityName) {
                if (null === $city->getSubCityName()) {
                    return false;
                }

                $normalized = u($city->getSubCityName())
                    ->trim()->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

                return $normalized === $normalizedCityName;
            });

            if (1 === count($matchingSubCities)) {
                return array_pop($matchingSubCities);
            }
        }

        // Try to find a main city with the same zip code
        if (!empty($zipCode)) {
            $mainCities = array_filter($cities, static function ($city) {
                return $city->isMainCity();
            });

            if (1 === count($mainCities)) {
                return array_pop($mainCities);
            }
        }

        // If no unique match is found, log and return null
        // TODO Ici il faut le lier a la 1ere ville trouvée
        // $this->output->writeln('No unique city found for zip code: ' . $zipCode . ' and city name: ' . $cityName);

        return null;
    }

    /**
     * Generate a unique canonical string for the RPPS entity.
     * The canonical format is "firstname-lastname-city-zipcode".
     * If duplicates are found, a numerical suffix is added to ensure uniqueness,
     * e.g., "anatole-cessot-neuilly-sur-seine-92200", "anatole-cessot-neuilly-sur-seine-92200-2".
     *
     * @throws RandomException
     */
    private function generateCanonical(RPPS $rpps): string
    {
        // Try to get city/zipcode from RPPSAddress first (preferred), then legacy fields
        $city = null;
        $zipcode = null;

        // Check if RPPS has addresses - use the first one available
        if (!$rpps->getAddresses()->isEmpty()) {
            $firstAddress = $rpps->getAddresses()->first();
            $city = $firstAddress->getCity();
            $zipcode = $firstAddress->getZipcode();
        }

        // Fallback to legacy fields if no address data
        if (!$city && !$zipcode) {
            $city = $rpps->getCity();
            $zipcode = $rpps->getZipcode();
        }

        // Build parts array from available data
        $parts = array_filter([
            $rpps->getFirstName(),
            $rpps->getLastName(),
            $city,
            $zipcode,
        ], static fn ($p) => null !== $p && '' !== $p);

        // Fallback to idRpps if no usable data
        if (empty($parts)) {
            $idRpps = $rpps->getIdRpps();
            if ($idRpps) {
                $parts = ['rpps', $idRpps];
            } else {
                // Ultimate fallback with timestamp for guaranteed uniqueness
                $parts = ['unknown', 'user', (string) time(), (string) random_int(1000, 9999)];
            }
        }
        $base = u(implode('-', $parts))
            ->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();
        $base = trim($base, '-');

        // Ensure we have something to work with
        if ('' === $base) {
            $base = 'fallback-' . time();
        }

        $canonical = $base;
        $suffix = 1;

        // Handle duplicates with numerical suffix
        while ($this->canonicalExists($canonical)) {
            ++$suffix;
            $canonical = $base . '-' . $suffix;
        }

        // Cache the result
        $this->existingCanonicals[$canonical] = true;

        return $canonical;
    }

    private function canonicalExists(string $canonical): bool
    {
        // Hit cache
        if (isset($this->existingCanonicals[$canonical])) {
            return true;
        }

        // Trim cache si nécessaire
        $this->manageCanonicalCacheMemory();

        try {
            $exists = $this->em->getConnection()->fetchOne(
                'SELECT 1 FROM rpps WHERE canonical = ? LIMIT 1',
                [$canonical]
            );
        } catch (Throwable $e) {
            $this->output->writeln("<error>DB error on canonical check: {$e->getMessage()}</error>");

            return false;
        }

        if ($exists) {
            $this->existingCanonicals[$canonical] = true;

            return true;
        }

        return false;
    }

    private function computeAddressMd5Hex(?string $address, ?string $city, ?string $zip): string
    {
        $normAddr = $this->normalizeText($address);
        $normCity = $this->normalizeText($city);
        $normZip = $this->normalizeText($zip);

        $toHash = $normAddr . '|' . $normCity . '|' . $normZip;

        return md5($toHash);
    }

    private function normalizeText(?string $value): string
    {
        if (null === $value) {
            return '';
        }

        $v = trim(preg_replace('#\s+#', ' ', $value));
        if ('' === $v || '0' === $v) {
            return '';
        }

        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v);
        if (false === $ascii) {
            $ascii = $v;
        }

        return strtolower($ascii);
    }

    /**
     * Hard delete all addresses not updated in the current run (importId != current).
     *
     * @throws \Doctrine\DBAL\Exception
     */
    private function purgeStaleAddresses(): void
    {
        $currentImportId = $this->getImportId();
        $this->output->writeln(
            "<comment>Purging stale addresses for current import id: $currentImportId...</comment>"
        );

        $deleted = $this->em->getConnection()->executeStatement(
            'DELETE FROM rpps_address WHERE import_id != :currentImportId',
            ['currentImportId' => $currentImportId]
        );

        $this->output->writeln("<info>Stale addresses deleted: $deleted</info>");
    }

    private function manageCanonicalCacheMemory(): void
    {
        if (count($this->existingCanonicals) >= self::MAX_CANONICAL_CACHE_SIZE) {
            // Garde seulement les 25% les plus récents (stratégie LRU simplifiée)
            $keepCount = (int) (self::MAX_CANONICAL_CACHE_SIZE * 0.25);
            $this->existingCanonicals = array_slice($this->existingCanonicals, -$keepCount, null, true);

            $this->output->writeln(
                "<comment>Canonical cache cleared. Kept $keepCount entries to prevent memory issues.</comment>"
            );
        }
    }
}
