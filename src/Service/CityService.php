<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Department;
use App\Entity\Drug;
use App\Entity\Entity;
use App\Entity\Region;
use App\Enum\DepartmentType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;

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
    private int $nbMainCity = 0;
    private int $nbSubCity = 0;
    private int $nbFailedCity = 0;
    private int $nbChefLieu = 0;

    // Counters for population updates
    private int $nbSuccessPopulation = 0;
    private int $nbFailedPopulation = 0;

    private array $populationUpdates = [];
    private array $coordinateUpdates = [];

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

        if ($type === 'city') {
            $this->initializeMainCities($mainCities);
        }

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip the header row
            fgetcsv($handle, 1000, $separator);

            $lineCounter = 0;

            while (($data = fgetcsv($handle, 1000, $separator)) !== false) {
                $this->processLine($data, $type, $mainCities);

                $lineCounter++;
                if ($lineCounter % 500 === 0 && !$this->verbose) {
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
        $this->output->writeln("<info>===== Import Summary  =====</info>");

        $this->output->writeln("<info>Regions imported: $this->nbSuccessRegion</info>");

        $this->output->writeln("<info>Departments imported: $this->nbSuccessDepartment</info>");
        if ($this->nbFailedDepartment > 0) {
            $this->output->writeln("<error>Department import failures : $this->nbFailedDepartment</error>");
        }

        $this->output->writeln("<info>Cities imported successfully: $this->nbSuccessCity</info>");
        $this->output->writeln("<info>- Main cities: $this->nbMainCity</info>");
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
        $this->output->writeln("<info>===== Import Completed =====</info>");
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
        }
    }

    private function initializeMainCities(array &$mainCities): void
    {
        $essentialCities = [
            ['75000', 'PARIS', '75000', 'Paris', ''],
            ['13200', 'MARSEILLE', '13000', 'Marseille', ''],
            ['69380', 'LYON', '69000', 'Lyon', ''],
        ];

        foreach ($essentialCities as $cityData) {
            $this->processLine($cityData, 'city', $mainCities);
        }
        $this->flushBatch();
    }

    private function processLine(array $data, string $type, array &$mainCities): void
    {
        if ($type === 'region') {
            $this->processRegionData($data);
        } elseif ($type === 'department') {
            $this->processDepartmentData($data);
        } elseif ($type === 'city') {
            $this->processCity($data, $mainCities);
        } elseif ($type === 'population') {
            $this->processPopulation($data);
        } elseif ($type === 'coordinates') {
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

        $this->nbSuccessRegion++;
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
            $this->nbFailedDepartment++;
            return;
        }

        if ($type === 'DPT') {
            $type = 'department';
        }

        $departmentType = DepartmentType::tryFrom(strtolower($type));
        if ($departmentType === null) {
            $this->output->writeln("<error>Invalid department type: $type for department: $name</error>");
            $this->nbFailedDepartment++;
            return;
        }

        $department = new Department();
        $department->setCodeDepartment($codeDepartment);
        $department->setName($name);
        $department->setRegion($region);
        $department->setDepartmentType($departmentType);
        $department->importId = $this->getImportId();

        $this->em->persist($department);

        $this->nbSuccessDepartment++;

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

        // Determine the department code, accounting for overseas departments
        $codeDepartment = (str_starts_with($inseeCode, '97') || str_starts_with($inseeCode, '98'))
            ? substr($inseeCode, 0, 3)
            : substr($inseeCode, 0, 2);

        // Find the department based on the determined code
        $department = $this->em->getRepository(Department::class)->findOneBy(['codeDepartment' => $codeDepartment]);

        if (!$department) {
            $this->output->writeln(
                "<error>Department not found for city: $communeName with department code: $codeDepartment</error>"
            );
            $this->nbFailedCity++;
            return;
        }

        // Generate the canonical value
        $slugify = new Slugify();
        $mainCityCanonical = $inseeCode . '-' . $postalCode . '-' . $routingLabel;
        $subCityCanonical = $inseeCode . '-' . $postalCode . '-' . $routingLabel . '-' . $ligne5;

        $mainCitySlug = $slugify->slugify($mainCityCanonical, '-');
        $subCitySlug = $slugify->slugify($subCityCanonical, '-');

        // Check if this entry is a main city or a sub city
        $isMainCity = empty($ligne5);
        $mainCityKey = $normalizedCommuneName . '-' . $postalCode;

        // Arrondissement has different postal code
        if ($communeName === 'PARIS') {
            $mainCityKey = 'Paris-75000';
        }
        if ($communeName === 'LYON') {
            $mainCityKey = 'Lyon-69000';
        }
        if ($communeName === 'MARSEILLE') {
            $mainCityKey = 'Marseille-13000';
        }

        $mainCity = $mainCities[$mainCityKey] ?? null;

        if (!$mainCity) {
            $mainCity = new City();
            $mainCity->setCanonical($mainCitySlug);
            $mainCity->setRawName($communeName);
            $mainCity->setName($normalizedCommuneName);
            $mainCity->setInseeCode($inseeCode);
            $mainCity->setPostalCode($postalCode);
            $mainCity->setDepartment($department);
            $mainCity->importId = $this->getImportId();

            $this->em->persist($mainCity);
            $this->nbMainCity++;
            $this->nbSuccessCity++;

            // Track the main city
            $mainCities[$mainCityKey] = $mainCity;
        }

        if (!$isMainCity) {
            // Create the sub city and link it to the main city
            $subCity = new City();
            $subCity->setCanonical($subCitySlug);
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

            $this->nbSubCity++;
            $this->nbSuccessCity++;
        }

        // Only consider main cities for chef-lieu assignment
        if ($isMainCity && !$department->getChefLieu()) {
            $normalizedChefLieuName = $this->normalizeCityName($department->tempChefLieuName);

            if ($normalizedChefLieuName === $normalizedCommuneName) {
                $department->setChefLieu($mainCity);
                $this->em->persist($department);
                $this->nbChefLieu++;

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
        $isMainCity = empty($ligne5);

        if ($isMainCity) {
            $matchingCities = $this->em->getRepository(City::class)->findBy([
                'inseeCode' => $inseeCode,
                'postalCode' => $zipCode,
                'mainCity' => null
            ]);
        } else {
            $matchingCities = $this->em->getRepository(City::class)->findBy([
                'inseeCode' => $inseeCode,
                'postalCode' => $zipCode,
            ]);
        }

        // Check if no city was found
        if (!$matchingCities) {
            $this->output->writeln("<error>No match found for INSEE code: $inseeCode and Postal code: $zipCode</error>");
            return;
        }

        if (count($matchingCities) === 1) {
            $city = $matchingCities[0];
        } else {
            $city = null;

            foreach ($matchingCities as $matchingCity) {
                if ($isMainCity) {
                    if ($matchingCity->getRawName() === $nomCommunePostal) {
                        $city = $matchingCity;
                        break;
                    }
                } else {
                    if ($matchingCity->isSubCity() && $matchingCity->getRawSubName() === $ligne5) {
                        $city = $matchingCity;
                        break;
                    }
                }
            }
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

        // Search for the main city with this INSEE code
        $mainCity = $this->em->getRepository(City::class)->findOneBy([
            'inseeCode' => $inseeCode,
            'mainCity' => null
        ]);

        if ($mainCity) {
            // Update the population for the found main city
            $mainCity->setPopulation((int)$totalPopulation);
            $this->populationUpdates[] = $mainCity;
            $this->nbSuccessPopulation++;

            if (count($this->populationUpdates) >= $this->batchSize) {
                $this->flushBatch();
            }

            if ($this->verbose) {
                $this->output->writeln("<info>Updated population for city: {$mainCity->getName()} (INSEE: $inseeCode)</info>");
            }

            return;
        }

        // Try fallback methods to find the city
        if ($this->verbose) {
            $this->output->writeln("<comment>No main city found for INSEE code: $inseeCode. Attempting to match by city name: $communeName</comment>");
        }

        // Try to match by communeName in altName, subCityName, or subCityAltName
        $matchingCities = $this->em->getRepository(City::class)->createQueryBuilder('c')
            ->where('LOWER(c.name) = :name OR LOWER(c.rawName) = :name OR LOWER(c.subCityName) = :name OR LOWER(c.rawSubName) = :name')
            ->setParameter('name', strtolower($communeName))
            ->getQuery()
            ->getResult();

        $numMatches = count($matchingCities);

        if ($numMatches === 1) {
            // Unique match found, update population
            $city = $matchingCities[0];
            $city->setPopulation((int)$totalPopulation);
            $this->populationUpdates[] = $city;
            $this->nbSuccessPopulation++;

            if (count($this->populationUpdates) >= $this->batchSize) {
                $this->flushBatch();
            }

            if ($this->verbose) {
                $this->output->writeln("<info>Updated population for matched city based on name: {$city->getName()} (Matched with: $communeName, INSEE: $inseeCode)</info>");
            }
            return;
        } elseif ($numMatches > 1) {
            $this->output->writeln("<error>Ambiguous matches found for commune name: $communeName (INSEE: $inseeCode)</error>");
        } else {
            $this->output->writeln("<error>No main city found for INSEE code: $inseeCode and no city matched the commune name: $communeName</error>");
        }

        $this->nbFailedPopulation++;
    }

    private function normalizeCityName(string $name): string
    {
        // Remove leading articles at the start
        $name = preg_replace('/^(L |LE |LA |LES |DU |DE |DES |D )/i', '', $name);

        // Normalize the string: convert to lowercase, remove accents, and replace spaces with dashes
        $slugify = new Slugify(['separator' => '-', 'lowercase' => true, 'trim' => true]);
        $normalized = $slugify->slugify($name);

        // Capitalize the first letter of each word except certain lowercase words
        $normalized = preg_replace_callback(
            '/\b([a-z]+)\b/i',
            function ($matches) {
                $word = strtolower($matches[0]);
                // List of words that should remain in lowercase
                $lowercaseWords = ['de', 'du', 'des', 'sur', 'sous', 'et', 'aux', 'le', 'la', 'les', "-d-"];
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
}
