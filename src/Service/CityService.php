<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Department;
use App\Entity\Entity;
use App\Entity\Region;
use App\Enum\DepartmentType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;

use function Symfony\Component\String\u;

class CityService extends ImporterService
{
    private bool $verbose = false;
    private int $batchSize = 100;

    // Counters for region
    private int $nbSuccessRegion = 0;

    // Counters for department
    private int $nbSuccessDepartment = 0;
    private int $nbFailedDepartment = 0;

    // Counters for cities
    private int $nbSuccessCity = 0;
    private int $nbMergedCity = 0;
    private int $nbMainCity = 0;
    private int $nbSubCity = 0;
    private int $nbFailedCity = 0;
    private int $nbChefLieu = 0;

    // Counters for population updates
    private int $nbSuccessPopulation = 0;
    private int $nbFailedPopulation = 0;

    private array $populationUpdates = [];
    private array $coordinateUpdates = [];
    private array $canonicalMap = [];

    public function __construct(
        FileProcessor $fileProcessor,
        EntityManagerInterface $em
    ) {
        parent::__construct(City::class, $fileProcessor, $em);
    }

    public function setVerbose(bool $verbose): void
    {
        $this->verbose = $verbose;
    }

    public function importData(string $filePath, string $type, string $separator = ','): bool
    {
        if (!file_exists($filePath)) {
            $this->output->writeln('<error>File not found: ' . $filePath . '</error>');

            return false;
        }

        // Initialize the main cities array to keep track of main cities during import
        $mainCities = [];

        if ('city' === $type) {
            $this->initializeMainCities($mainCities);
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip the header row
            fgetcsv($handle, 1000, $separator);

            $lineCounter = 0;

            while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
                $this->processLine($data, $type, $mainCities);

                ++$lineCounter;
                if (0 === $lineCounter % 500 && !$this->verbose) {
                    // Output a progress message every 500 lines
                    $this->output->writeln("<comment>Processed $lineCounter lines...</comment>");
                }
            }

            fclose($handle);

            $this->flushBatch();
        } else {
            $this->output->writeln('<error>Unable to open file: ' . $filePath . '</error>');

            return false;
        }

        $this->em->flush();

        $this->printFinalStat();

        return true;
    }

    private function printFinalStat(): void
    {
        $this->output->writeln('<info>===== Import Summary  =====</info>');

        $this->output->writeln("<info>Regions imported: $this->nbSuccessRegion</info>");

        $this->output->writeln("<info>Departments imported: $this->nbSuccessDepartment</info>");
        if ($this->nbFailedDepartment > 0) {
            $this->output->writeln("<error>Department import failures : $this->nbFailedDepartment</error>");
        }

        $this->output->writeln("<info>Cities imported successfully: $this->nbSuccessCity</info>");
        $this->output->writeln("<info>- Main cities: $this->nbMainCity</info>");
        $this->output->writeln("<info>- Merged cities: $this->nbMergedCity</info>");
        $this->output->writeln("<info>- Subcities: $this->nbSubCity</info>");
        $this->output->writeln("<info>- ChefLieu: $this->nbChefLieu</info>");

        if ($this->nbFailedCity > 0) {
            $this->output->writeln("<error>City import failures : $this->nbFailedCity</error>");
        }

        $this->output->writeln("<info>Population updates successful: $this->nbSuccessPopulation</info>");
        if ($this->nbFailedPopulation > 0) {
            $this->output->writeln("<error>Population update failures : $this->nbFailedPopulation</error>");
        }

        // General completion message
        $this->output->writeln('<info>===== Import Completed =====</info>');
    }

    private function flushBatch(): void
    {
        if (!empty($this->coordinateUpdates)) {
            if ($this->output->isVerbose()) {
                $this->output->writeln('Flushing batch of ' . count($this->coordinateUpdates) . ' coordinate updates.');
            }
            foreach ($this->coordinateUpdates as $city) {
                $this->em->persist($city);
            }
            $this->em->flush();
            $this->coordinateUpdates = [];
            $this->em->clear(); // Clear the entity manager to free up memory
        }

        if (!empty($this->populationUpdates)) {
            if ($this->output->isVerbose()) {
                $this->output->writeln('Flushing batch of ' . count($this->populationUpdates) . ' population updates.');
            }
            foreach ($this->populationUpdates as $city) {
                $this->em->persist($city);
            }
            $this->em->flush();
            $this->populationUpdates = [];
            $this->em->clear(); // Clear the entity manager to free up memory
        }
    }

    private function initializeMainCities(array &$mainCities): void
    {
        $essentialCities = [
            ['75056', 'PARIS', '75000', 'Paris', ''],
            ['13055', 'MARSEILLE', '13000', 'Marseille', ''],
            ['69123', 'LYON', '69000', 'Lyon', ''],
        ];

        foreach ($essentialCities as $cityData) {
            $this->processLine($cityData, 'city', $mainCities);
        }
        $this->flushBatch();
    }

    private function processLine(array $data, string $type, array &$mainCities): void
    {
        if ('region' === $type) {
            $this->processRegionData($data);
        } elseif ('department' === $type) {
            $this->processDepartmentData($data);
        } elseif ('city' === $type) {
            $this->processCity($data, $mainCities);
        } elseif ('population' === $type) {
            $this->processPopulation($data);
        } elseif ('coordinates' === $type) {
            $this->processCityCoordinates($data);
        } else {
            $this->output->writeln('<error>Unknown type: ' . $type . '</error>');
        }
    }

    private function processRegionData(array $data): void
    {
        [$codeRegion, $name] = $data;

        $region = new Region();
        $region->setCodeRegion($codeRegion);
        $region->setName($name);
        $region->importId = $this->getImportId();

        $this->em->persist($region);

        ++$this->nbSuccessRegion;
        if ($this->verbose) {
            $this->output->writeln("<info>Imported region: $name</info>");
        }
    }

    private function processDepartmentData(array $data): void
    {
        [
            $codeDepartment,
            $type,
            $name,
            $chefLieuName,
            $regionName,
            $debutValidite,
            $finValidite
        ] = $data;

        $region = $this->em->getRepository(Region::class)->findOneBy(['name' => $regionName]);
        if (!$region) {
            $this->output->writeln("<error>Region not found: $regionName for department: $name</error>");
            ++$this->nbFailedDepartment;

            return;
        }

        if ('DPT' === $type) {
            $type = 'department';
        }

        $departmentType = DepartmentType::tryFrom(strtolower($type));
        if (null === $departmentType) {
            $this->output->writeln("<error>Invalid department type: $type for department: $name</error>");
            ++$this->nbFailedDepartment;

            return;
        }

        $department = new Department();
        $department->setCodeDepartment($codeDepartment);
        $department->setName($name);
        $department->setRegion($region);
        $department->setDepartmentType($departmentType);
        $department->importId = $this->getImportId();

        $this->em->persist($department);

        ++$this->nbSuccessDepartment;

        $department->tempChefLieuName = $chefLieuName;

        if ($this->verbose) {
            $this->output->writeln("<info>Imported department: $name</info>");
        }
    }

    private function processCity(array $data, array &$mainCities): void
    {
        [
            $inseeCode,          // code_commune_INSEE
            $communeName,        // Nom_de_la_commune
            $postalCode,         // Code_postal
            $routingLabel,       // Libellé_d_acheminement
            $ligne5,             // Ligne_5 (optional, often empty)
        ] = $data;

        // Normalize the city name (handle "ST" to "Saint" and other abbreviations)
        $normalizedCommuneName = $this->normalizeCityName($communeName);

        // Almost always the same as the commune name, but sometimes different specially for the Dom-Tom
        $normalizedRoutingLabel = $this->normalizeCityName($routingLabel);

        // Determine the department code, accounting for overseas departments
        $codeDepartment = (str_starts_with($inseeCode, '97') || str_starts_with($inseeCode, '98'))
            ? substr($inseeCode, 0, 3)
            : substr($inseeCode, 0, 2);

        $department = $this->em->getRepository(Department::class)->findOneBy(['codeDepartment' => $codeDepartment]);

        if (!$department) {
            $this->output->writeln(
                "<error>Department not found for city: $communeName with department code: $codeDepartment</error>"
            );
            ++$this->nbFailedCity;

            return;
        }

        // Generate the canonical value
        $canonicalMain = $this->slugify($normalizedRoutingLabel);

        // Check if this entry is a main city or a sub city
        $isMainCity = empty($ligne5);
        $mainCityKey = $normalizedCommuneName . '-' . $inseeCode;
        if (!$isMainCity) {
            $mainCityKey = $this->handleArrondissements($normalizedCommuneName, $mainCityKey);
        }
        $mainCity = $mainCities[$mainCityKey] ?? null;

        if (!$mainCity) {
            $canonical = $canonicalMain;
            // Checking for duplicate against the hashmap
            if (isset($this->canonicalMap[$canonical])) {
                // Add the postal code to resolve the duplicate
                $canonical = $canonical . '-' . $postalCode;
            }

            // Store the canonical in the hashmap to ensure future uniqueness checks
            $this->canonicalMap[$canonical] = true;

            $mainCity = new City();
            $mainCity->setCanonical($canonical);
            $mainCity->setRawName($communeName);
            $mainCity->setName($normalizedCommuneName);
            $mainCity->setInseeCode($inseeCode);
            $mainCity->setPostalCode($postalCode); // First postal code set as the main one
            $mainCity->setDepartment($department);
            $mainCity->importId = $this->getImportId();

            $this->em->persist($mainCity);
            ++$this->nbMainCity;
            ++$this->nbSuccessCity;

            // Track the main city
            $mainCities[$mainCityKey] = $mainCity;
        } elseif ($isMainCity) {
            if ($this->verbose) {
                $this->output->writeln("<info>Adding additional postal code: $postalCode for city: {$mainCity->getName()} (INSEE: $inseeCode)</info>");
            }

            // Add the additional postal code to the existing city
            $mainCity->addAdditionalPostalCode($postalCode);
            $this->nbMergedCity++;
        }

        if (!$isMainCity) {
            $normalizedLigne5 = $this->slugify($this->normalizeCityName($ligne5));

            // Add main city to the slug for subcities except for arrondissements
            $canonicalSub = $canonicalMain . '-' . $normalizedLigne5;
            $canonicalSub = $this->handleArrondissementsSlug($normalizedCommuneName, $normalizedLigne5, $canonicalSub);

            // Checking for duplicate against the hashmap
            if (isset($this->canonicalMap[$canonicalSub])) {
                // Add the postal code to resolve the duplicate
                $canonicalSub = $canonicalSub . '-' . $postalCode;
            }

            $this->canonicalMap[$canonicalSub] = true;

            // Create the sub city and link it to the main city
            $subCity = new City();
            $subCity->setCanonical($canonicalSub);
            $subCity->setRawName($communeName);
            $subCity->setRawSubName($ligne5);
            $subCity->setName($normalizedCommuneName);
            $subCity->setSubCityName($this->normalizeCityName($ligne5));
            $subCity->setInseeCode($inseeCode);
            $subCity->setPostalCode($postalCode);
            $subCity->setDepartment($department);
            $subCity->setMainCity($mainCity);
            $subCity->importId = $this->getImportId();

            $this->em->persist($subCity);
            $mainCity->addSubCity($subCity);

            ++$this->nbSubCity;
            ++$this->nbSuccessCity;
        }

        // Chef-lieu assignment
        if (!$department->getChefLieu()) {
            $normalizedChefLieuName = $this->normalizeCityName($department->tempChefLieuName);

            if ($normalizedChefLieuName === $mainCity->getName()) {
                $department->setChefLieu($mainCity);
                $this->em->persist($department);
                ++$this->nbChefLieu;

                if ($this->verbose) {
                    $this->output->writeln("<info>Set chef-lieu for department: {$department->getName()} to city: $communeName</info>");
                }
            }
        }
    }

    private function processCityCoordinates(array $data): void
    {
        [
            $inseeCode,           // code_commune_INSEE
            $nomCommunePostal,    // nom_commune_postal
            $zipCode,             // code_postal
            $libelleAcheminement, // libelle_acheminement
            $ligne5,              // ligne_5
            $latitude,            // latitude
            $longitude,           // longitude
            $codeCommune,         // code_commune
            $article,             // article
            $nomCommune,          // nom_commune
            $nomCommuneComplet,   // nom_commune_complet
            $codeDepartment,      // code_departement
            $departmentName,      // nom_departement
            $codeRegion,          // code_region
            $regionName           // nom_region
        ] = $data;

        // Format postal code and INSEE code to ensure consistency
        $zipCode = str_pad($zipCode, 5, '0', STR_PAD_LEFT);
        $inseeCode = str_pad($inseeCode, 5, '0', STR_PAD_LEFT);

        /** @var CityRepository $cityRepo */
        $cityRepo = $this->em->getRepository(City::class);
        $matchingCities = $cityRepo->findCitiesByInseeAndPostalCode($inseeCode, $zipCode);

        // Check if no city was found
        if (!$matchingCities) {
            $this->output->writeln("<error>No match found for INSEE code: $inseeCode and Postal code: $zipCode</error>");

            return;
        }

        if (1 === count($matchingCities)) {
            $city = $matchingCities[0];
        } else {
            $city = null;

            // 1st attempt to match, should be a safe match.
            foreach ($matchingCities as $matchingCity) {
                if ($matchingCity->isSubCity() && !empty($ligne5) && $matchingCity->getRawSubName() === $ligne5) {
                    $city = $matchingCity;
                    break;
                }

                if ($matchingCity->getRawName() === $nomCommunePostal) {
                    $city = $matchingCity;
                    break;
                }
            }
        }

        if (!$city) {
            // 2nd attempt to match, less safe
            foreach ($matchingCities as $matchingCity) {
                if ($matchingCity->getRawSubName() === $nomCommunePostal) {
                    $city = $matchingCity;
                    break;
                }
            }
        }

        if (!$city) {
            // 3rd attempt to match, random
            $city = $matchingCities[0];
        }

        if ($city) {
            $city->setLatitude($latitude ?: null);
            $city->setLongitude($longitude ?: null);
            $city->setName($nomCommuneComplet);

            $this->coordinateUpdates[] = $city;

            if (count($this->coordinateUpdates) >= $this->batchSize) {
                $this->flushBatch();
            }

            if ($this->verbose) {
                $this->output->writeln("<info>Updated coordinates for city: {$city->getName()} (INSEE: $inseeCode)</info>");
            }

            return;
        }

        $this->output->writeln(
            "<error>Multiple matches for INSEE code: $inseeCode and postal code: $zipCode, and further matching failed.</error>"
        );
    }

    private function processPopulation(array $data): void
    {
        [
            $inseeCode,       // DEPCOM (INSEE Code)
            $communeName,     // COM (Commune Name)
            $municipalPopulation, // PMUN (Municipal Population)
            $capitalPopulation,   // PCAP (Capital Population)
            $totalPopulation  // PTOT (Total Population)
        ] = $data;

        // Get matching city by INSEE code
        $cities = $this->em->getRepository(City::class)->findBy(['inseeCode' => $inseeCode]);

        $numMatches = count($cities);

        // Easy case: only one city matches the INSEE code
        if ($numMatches === 1) {
            $city = $cities[0];
            $city->setPopulation((int)$totalPopulation);
            $this->nbSuccessPopulation++;
            return;
        }

        // Multiple cities found, proceed with sub-city and main-city separation
        $cityName = $this->slugify($this->normalizeCityName($communeName));

        // Separate cities into sub-cities and main cities
        $mainCities = [];
        $subCities = [];

        foreach ($cities as $city) {
            if ($city->isMainCity()) {
                $mainCities[] = $city;
            } else {
                $subCities[] = $city;
            }
        }

        foreach ($subCities as $city) {
            $subCityMatch = $this->slugify($this->normalizeCityName($city->getRawSubName()));

            if ($subCityMatch === $cityName) {
                $city->setPopulation((int)$totalPopulation);
                $this->nbSuccessPopulation++;
                return;
            }
        }

        foreach ($mainCities as $city) {
            $mainCityMatch = $this->slugify($this->normalizeCityName($city->getRawName()));

            if ($mainCityMatch === $cityName) {
                $city->setPopulation((int)$totalPopulation);
                $this->nbSuccessPopulation++;
                return;
            }
        }

        // Insee code does not match any city - try to match by name

        $matchingCities = $this->em->getRepository(City::class)->createQueryBuilder('c')
            ->where(
                'LOWER(c.name) = :name OR LOWER(c.rawName) = :name OR LOWER(c.subCityName) = :name OR LOWER(c.rawSubName) = :name'
            )
            ->setParameter('name', strtolower($communeName))
            ->getQuery()
            ->getResult();

        $numMatches = count($matchingCities);

        if ($numMatches === 1) {
            $city = $matchingCities[0];

            // Check if the current population is zero before updating
            if ((int) $city->getPopulation() > 0) {
                $this->output->writeln("<error>Warning: Current population for city {$city->getName()} (INSEE: $inseeCode) is not zero :" . $city->getPopulation() . ". Please review this case for potential issues.</error>");
                $this->nbFailedPopulation++;
                return;
            }

            // Unique match found, update population
            $city->setPopulation((int)$totalPopulation);
            $this->nbSuccessPopulation++;

            if ($this->verbose) {
                $this->output->writeln("<info>Updated population for matched city based on name: {$city->getName()} (Matched with: $communeName, INSEE: $inseeCode)</info>");
            }

            return;
        } elseif ($numMatches > 1) {
            $this->output->writeln("<error>Ambiguous matches found for commune name: $communeName (INSEE: $inseeCode)</error>");
        } else {
            $this->output->writeln("<error>No main city found for INSEE code: $inseeCode and no city matched the commune name: $communeName</error>");
        }

        ++$this->nbFailedPopulation;
    }

    public function aggregatePopulationForMainCities(): void
    {
        // Fetch all main cities (mainCity is NULL) with no population set (population is NULL)
        $mainCities = $this->em->getRepository(City::class)->createQueryBuilder('c')
            ->where('c.mainCity IS NULL')
            ->andWhere('c.population IS NULL')
            ->getQuery()
            ->getResult();

        // Iterate through each main city and calculate the total population of its subtitles
        /** @var City $mainCity */
        foreach ($mainCities as $mainCity) {
            $subCities = $mainCity->getSubCities();
            $totalPopulation = 0;
            foreach ($subCities as $subCity) {
                if ($subCity->getPopulation() !== null) {
                    $totalPopulation += $subCity->getPopulation();
                }
            }

            if ($totalPopulation > 0) {
                $mainCity->setPopulation($totalPopulation);
                $this->em->persist($mainCity);

                if ($this->verbose) {
                    $this->output->writeln("<info>Updated population for main city: {$mainCity->getName()} to $totalPopulation</info>");
                }
            }
        }

        $this->em->flush();
        $this->output->writeln("<info>Population aggregation completed for main cities.</info>");
    }


    private function normalizeCityName(string $name): string
    {
        // Remove leading articles at the start
        $name = preg_replace('/^(L |LE |LA |LES |DU |DE |DES |D )/i', '', $name);

        // Normalize the string: convert to lowercase, remove accents, and replace spaces with dashes
        $normalized = $this->slugify($name);

        // Capitalize the first letter of each word except certain lowercase words
        $normalized = preg_replace_callback(
            '/\b([a-z]+)\b/i',
            function ($matches) {
                $word = strtolower($matches[0]);
                // List of words that should remain in lowercase
                $lowercaseWords = ['de', 'du', 'des', 'sur', 'sous', 'et', 'aux', 'le', 'la', 'les', '-d-'];

                return in_array($word, $lowercaseWords) ? $word : ucfirst($word);
            },
            $normalized
        );

        // List of replacements to expand abbreviations and fix specific cases post-normalization
        $replacements = [
            '\b-st-\b' => '-Saint-',    // Replace '-st-' as a whole word with 'Saint'
            '\b-ste-\b' => '-Sainte-',  // Replace '-ste-' as a whole word with 'Sainte'
            '-d-' => "-d'",           // Replace '-d-' with " d'"
            '^st-' => 'Saint-',       // Replace 'st-' at the start with 'Saint '
            '^ste-' => 'Sainte-',     // Replace 'ste-' at the start with 'Sainte '
        ];

        // Apply replacements to expand abbreviations to their full forms
        foreach ($replacements as $search => $replace) {
            $normalized = preg_replace('/' . $search . '/i', $replace, $normalized);
        }

        // Return the formatted city name with corrections applied
        return trim($normalized);
    }

    public function purgeAllData(): void
    {
        // Step 0: Clear all City references in RPPS
        $this->em->createQuery('UPDATE App\Entity\RPPS r SET r.cityEntity = NULL')->execute();
        $this->output->writeln('<info>Cleared all city references in RPPS.</info>');

        // Step 1: Clear all chef-lieu references in departments
        $this->em->createQuery('UPDATE App\Entity\Department d SET d.chefLieu = NULL')->execute();
        $this->output->writeln('<info>Cleared all chef-lieu references in departments.</info>');

        // Step 2: Delete subcities first to avoid foreign key constraint violations
        $this->em->createQuery('DELETE FROM App\Entity\City c WHERE c.mainCity IS NOT NULL')->execute();
        $this->output->writeln('<info>All subcities have been purged.</info>');

        // Step 3: Delete main cities after subcities
        $this->em->createQuery('DELETE FROM App\Entity\City c WHERE c.mainCity IS NULL')->execute();
        $this->output->writeln('<info>All main cities have been purged.</info>');

        // Step 4: Delete all department data
        $this->em->createQuery('DELETE FROM App\Entity\Department')->execute();
        $this->output->writeln('<info>All departments have been purged.</info>');

        // Step 5: Delete all region data
        $this->em->createQuery('DELETE FROM App\Entity\Region')->execute();
        $this->output->writeln('<info>All regions have been purged.</info>');
    }

    public function purgePopulation(): void
    {
        $this->em->createQuery('UPDATE App\Entity\City c SET c.population = NULL')->execute();
        $this->output->writeln('<info>Population data purged for all cities.</info>');
    }

    public function purgeCoordinates(): void
    {
        $this->em->createQuery('UPDATE App\Entity\City c SET c.latitude = NULL, c.longitude = NULL')->execute();
        $this->output->writeln('<info>Coordinate data purged for all cities.</info>');
    }

    protected function processData(array $data, string $type): ?Entity
    {
        return null;
    }

    private function handleArrondissements(string $normalizedCommuneName, string $mainCityKey): string
    {
        if ($normalizedCommuneName === 'Paris') {
            return 'Paris-75056';
        } elseif ($normalizedCommuneName === 'Marseille') {
            return 'Marseille-13055';
        } elseif ($normalizedCommuneName === 'Lyon') {
            return 'Lyon-69123';
        }

        return $mainCityKey;
    }

    private function handleArrondissementsSlug(string $normalizedCommuneName, string $ligne5, string $canonicalSub): string
    {
        if ($normalizedCommuneName === 'Paris') {
            return $ligne5;
        } elseif ($normalizedCommuneName === 'Marseille') {
            return $ligne5;
        } elseif ($normalizedCommuneName === 'Lyon') {
            return $ligne5;
        }

        return $canonicalSub;
    }

    private function slugify(string $string): string
    {
        return u($string)
            ->trim()                // Remove whitespace from the beginning and end
            ->lower()               // Convert to lowercase
            ->ascii()               // Convert to ASCII, removing accents
            ->replace('_', '-')      // Replace underscores with hyphens
            ->replace(' ', '-')      // Replace spaces with hyphens
            ->replace('--', '-')     // Replace double hyphens with a single hyphen
            ->toString();            // Convert back to a string
    }
}
