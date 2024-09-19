<?php

namespace App\Service;

use App\DataFixtures\LoadRPPS;
use App\Entity\City;
use App\Entity\RPPS;
use Cocur\Slugify\Slugify;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Contains all useful methods to process files and import them into database.
 */
class RPPSService extends ImporterService
{
    private int $matchedCitiesCount = 0;
    private int $unmatchedCitiesCount = 0;

    public function __construct(
        protected readonly string $cps,
        protected readonly string $rpps,
        FileProcessor $fileProcessor,
        EntityManagerInterface $em,
        private readonly KernelInterface $kernel
    ) {
        parent::__construct(RPPS::class, $fileProcessor, $em);
    }

    public function loadTestData(): void
    {
        $this->output->writeln('Deletion of existing data in progress');

        $ids = [
            '21234567890',
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
     */
    protected function processData(array $data, string $type): ?RPPS
    {
        return match ($type) {
            'cps' => $this->processCPS($data),
            'rpps' => $this->processRPPS($data),
            default => throw new Exception("Type $type is not supported yet"),
        };
    }

    /**
     * @throws NonUniqueResultException
     */
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
        $rpps->setTitle($data[6]);
        $rpps->setLastName($data[7]);
        $rpps->setFirstName($data[8]);
        $rpps->setSpecialty($data[10]);

        if ($data[16] && in_array($data[13], ['S', 'CEX'])) {
            $rpps->setSpecialty($data[16]);
        }

        $rpps->setAddress($data[28] . ' ' . $data[31] . ' ' . $data[31] . ' ' . $data[33]);
        $rpps->setZipcode($data[35]);
        $rpps->setCity($data[37]);

        $cityEntity = $this->findCityEntity($data[35], $data[37]);
        if ($cityEntity) {
            $rpps->setCityEntity($cityEntity);
            $this->matchedCitiesCount++;
        } else {
            $this->unmatchedCitiesCount++;
        }

        $rpps->setPhoneNumber(str_replace(' ', '', (string) $data[40]));
        $rpps->setEmail($data[43]);
        $rpps->setFinessNumber($data[21]);

        $rpps->importId = $this->getImportId();

        $this->entities[$rpps->getIdRpps()] = $rpps;

        return $rpps;
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


        if (count($cities) === 1) {
            // If only one city found, return it
            return $cities[0];
        }

        // Try to find a city matching the normalized name
        if ($cityName) {
            $slugify = new Slugify(['separator' => '-', 'lowercase' => true, 'trim' => true]);
            $normalizedCityName = $slugify->slugify($cityName);

            // Check for matching city name
            $matchingNameCities = array_filter($cities, function ($city) use ($normalizedCityName, $slugify) {
                return $slugify->slugify($city->getName()) === $normalizedCityName;
            });

            if (count($matchingNameCities) === 1) {
                return array_pop($matchingNameCities);
            }

            // From the cities with same name, is there a unique main city?
            $matchingNameMainCities = array_filter($matchingNameCities, function ($city) {
                return $city->isMainCity();
            });

            if (count($matchingNameMainCities) === 1) {
                return array_pop($matchingNameMainCities);
            }


            // Check for matching sub-city name
            $matchingSubCities = array_filter($matchingNameCities, function ($city) use ($normalizedCityName, $slugify) {
                if (null === $city->getSubCityName()) {
                    return false;
                }
                return $slugify->slugify($city->getSubCityName()) === $normalizedCityName;
            });

            if (count($matchingSubCities) === 1) {
                return array_pop($matchingSubCities);
            }
        }

        // Try to find a main city with the same zip code
        if (!empty($zipCode)) {
            $mainCities = array_filter($cities, function ($city) {
                return $city->isMainCity();
            });

            if (count($mainCities) === 1) {
                return array_pop($mainCities);
            }
        }

        // If no unique match is found, log and return null
        //$this->output->writeln('No unique city found for zip code: ' . $zipCode . ' and city name: ' . $cityName);

        return null;
    }
}
