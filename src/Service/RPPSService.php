<?php

namespace App\Service;

use App\DataFixtures\LoadRPPS;
use App\Entity\City;
use App\Entity\RPPS;
use App\Entity\Specialty;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use function Symfony\Component\String\u;

/**
 * Contains all useful methods to process files and import them into database.
 */
class RPPSService extends ImporterService
{
    private int $matchedCitiesCount = 0;
    private int $unmatchedCitiesCount = 0;

    // Hashmaps for specialties to avoid unnecessary DB queries
    private array $specialtyByName = [];  // The name field of the Specialty entity
    private array $specialtyByAltName = []; // Mapping of Instamed RPPS db specialties to our specialties
    private array $existingCanonicals = []; // Hashmap to store existing canonicals to avoid duplicate queries

    public function __construct(
        protected readonly string $cps,
        protected readonly string $rpps,
        FileProcessor $fileProcessor,
        EntityManagerInterface $em,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct(RPPS::class, $fileProcessor, $em);
        $this->initializeSpecialtyMaps();
        //      $this->initializeCanonicalMap();
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

    // Initialize the hashmap for existing canonicals
    /* private function initializeCanonicalMap(): void
     {
         $existingCanonicals = $this->em->getRepository(RPPS::class)->createQueryBuilder('r')
             ->select('r.canonical')
             ->getQuery()
             ->getResult();

         foreach ($existingCanonicals as $entry) {
             $this->existingCanonicals[$entry['canonical']] = true;
         }
     } */

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
            $ids[] = "1{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}";
            $ids[] = "2{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}";
        }

        $this->em->getConnection()->executeQuery(
            'DELETE FROM rpps WHERE id_rpps IN (:ids)',
            ['ids' => $ids],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        $this->output->writeln('Existing data successfully deleted');

        $loader = new ContainerAwareLoader($this->kernel->getContainer());

        $fixture = new LoadRPPS();
        $fixture->importId = $this->getImportId();
        $loader->addFixture($fixture);

        $executor = new ORMExecutor($this->em);
        $executor->execute($loader->getFixtures(), true);

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
            throw new Exception("Type $type not working");
        }

        $process = $this->processFile($output, $file, $type, $options, $start, $limit);

        // unlink($file);

        $this->output->writeln('Total Matched Cities: ' . $this->matchedCitiesCount);
        $this->output->writeln('Total Unmatched Cities: ' . $this->unmatchedCitiesCount);

        return $process;
    }

    /**
     * @throws NonUniqueResultException
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

    protected function processRPPS(array $data): ?RPPS
    {
        $rpps = $this->entities[$data[1]] ?? $this->repository->find($data[1]);

        if (!($rpps instanceof RPPS)) {
            $rpps = new RPPS();
        }

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

        $rpps->setAddress($data[28] . ' ' . $data[31] . ' ' . $data[32]);
        $rpps->setAddressExtension($data[33]);
        $rpps->setZipcode($data[35]);
        $rpps->setCity($data[37]);

        $cityEntity = $this->findCityEntity($data[35], $data[37]);
        if ($cityEntity) {
            $rpps->setCityEntity($cityEntity);
            ++$this->matchedCitiesCount;
        } else {
            ++$this->unmatchedCitiesCount;
        }

        $rpps->setPhoneNumber(str_replace(' ', '', (string) $data[40]));
        $rpps->setEmail($data[43]);
        $rpps->setFinessNumber($data[21]);

        // Set canonical only if it is not already set
        if (!$rpps->getId() || !$rpps->getCanonical()) {
            $canonical = $this->generateCanonical($rpps);
            $rpps->setCanonical($canonical);
        }
        $rpps->importId = $this->getImportId();

        $this->entities[$rpps->getIdRpps()] = $rpps;

        return $rpps;
    }

    private function findSpecialtyEntity(string $specialtyName): ?Specialty
    {
        $specialtyName = trim($specialtyName);

        // Check for exact match
        if (isset($this->specialtyByName[$specialtyName])) {
            // Fetch from DB to ensure we have the most up-to-date entity,
            // avoiding memory overhead of storing full entities in the hashmap.
            // If we keep assigning the same entity in batch processing, form the hashmap value, somehow doctrine will not be happy.
            return $this->em->getRepository(Specialty::class)->findOneBy(['name' => $specialtyName]);
        }

        // Check for alternative name match using the static array
        if (isset($this->specialtyByAltName[$specialtyName])) {
            return $this->em->getRepository(Specialty::class)->findOneBy(['name' => $this->specialtyByAltName[$specialtyName]]);
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
            // Find by  city name (lowercase comparison)
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
            $normalizedCityName = u($cityName)->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

            // Check for matching city name
            $matchingNameCities = array_filter($cities, function ($city) use ($normalizedCityName) {
                $n2 = u($city->getName())->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

                return $n2 === $normalizedCityName;
            });

            if (1 === count($matchingNameCities)) {
                return array_pop($matchingNameCities);
            }

            // From the cities with same name, is there a unique main city?
            $matchingNameMainCities = array_filter($matchingNameCities, function ($city) {
                return $city->isMainCity();
            });

            if (1 === count($matchingNameMainCities)) {
                return array_pop($matchingNameMainCities);
            }

            // Check for matching sub-city name
            $matchingSubCities = array_filter($matchingNameCities, function ($city) use ($normalizedCityName) {
                if (null === $city->getSubCityName()) {
                    return false;
                }

                $normalized = u($city->getSubCityName())->trim()->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

                return $normalized === $normalizedCityName;
            });

            if (1 === count($matchingSubCities)) {
                return array_pop($matchingSubCities);
            }
        }

        // Try to find a main city with the same zip code
        if (!empty($zipCode)) {
            $mainCities = array_filter($cities, function ($city) {
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
     */
    private function generateCanonical(RPPS $rpps): string
    {
        $canonicalBase = u(implode('-', [
            $rpps->getFirstName(),
            $rpps->getLastName(),
            $rpps->getCity(),
            $rpps->getZipcode(),
        ]))->lower()->ascii()->replace('_', '-')->replace(' ', '-')->replace('--', '-')->toString();

        $canonicalBase = trim($canonicalBase, '-');
        $canonical = $canonicalBase;
        $suffix = 1;

        // Check if canonical already exists and add suffix if needed
        while ($this->canonicalExists($canonical)) {
            ++$suffix;
            $canonical = $canonicalBase . '-' . $suffix;
        }

        // Add the generated canonical to the hashmap to prevent future duplicates
        $this->existingCanonicals[$canonical] = true;

        return $canonical;
    }

    private function canonicalExists(string $canonical): bool
    {
        if (isset($this->existingCanonicals[$canonical])) {
            return true;
        }

        $existing = $this->em->getConnection()->fetchOne('SELECT 1 FROM rpps WHERE canonical = ?', [$canonical]);

        if ($existing) {
            $this->existingCanonicals[$canonical] = true;

            return true;
        }

        return false;
    }
}
