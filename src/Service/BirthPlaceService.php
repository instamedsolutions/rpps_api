<?php

namespace App\Service;

use App\DTO\BirthPlaceDTO;
use App\Entity\InseeCommune;
use App\Entity\InseeCommune1943;
use App\Entity\InseePays;
use App\Entity\InseePays1943;
use App\Repository\InseeCommune1943Repository;
use App\Repository\InseeCommuneRepository;
use App\Repository\InseePays1943Repository;
use App\Repository\InseePaysRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class BirthPlaceService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Search for birthplaces without a date filter.
     */
    public function searchBirthPlaces(string $search): array
    {
        /** @var InseeCommuneRepository $communeRepository */
        $communeRepository = $this->em->getRepository(InseeCommune::class);
        /** @var InseePaysRepository $paysRepository */
        $paysRepository = $this->em->getRepository(InseePays::class);

        $communeResults = $communeRepository->searchByName($search);
        $paysResults = $paysRepository->searchByName($search);

        return $this->mapResultsToDTO($communeResults, $paysResults);
    }

    public function getBirthPlaceByCode(string $code, ?string $dateOfBirth): ?BirthPlaceDTO
    {
        if ($dateOfBirth) {
            try {
                $dateOfBirth = new DateTime($dateOfBirth);
            } catch (Exception $e) {
                // If the date is invalid, we ignore it and proceed with the search
                $dateOfBirth = null;
            }
        }

        if ($dateOfBirth) {
            $communeRepo = $this->em->getRepository(InseeCommune1943::class);
            $paysRepo = $this->em->getRepository(InseePays1943::class);
        } else {
            $communeRepo = $this->em->getRepository(InseeCommune::class);
            $paysRepo = $this->em->getRepository(InseePays::class);
        }

        if ($dateOfBirth) {
            // @phpstan-ignore-next-line
            $commune = $communeRepo->findOneByCodeAndDate($code, $dateOfBirth);
        } else {
            $commune = $communeRepo->findOneBy(['codeCommune' => $code]);
        }
        if ($commune) {
            return $this->buildDto($commune);
        }

        if ($dateOfBirth) {
            // @phpstan-ignore-next-line
            $pays = $paysRepo->findOneByCodeAndDate($code, $dateOfBirth);
        } else {
            $pays = $paysRepo->findOneBy(['codePays' => $code]);
        }
        if ($pays) {
            return $this->buildDto($pays);
        }

        return null;
    }

    /**
     * Search for birthplaces filtered by date.
     */
    public function searchBirthPlacesByDate(string $search, DateTime $date): array
    {
        // seuil INSEE : 1er janvier 1943
        $threshold = new DateTime('1943-01-01');

        if ($date < $threshold) {
            $date = $threshold;
        }

        // sinon, DOB >= 1943 → interroger la base historique avec dates
        /** @var InseeCommune1943Repository $commune1943Repository */
        $commune1943Repository = $this->em->getRepository(InseeCommune1943::class);
        /** @var InseePays1943Repository $pays1943Repository */
        $pays1943Repository = $this->em->getRepository(InseePays1943::class);

        $communeResults = $commune1943Repository->searchByNameAndDate($search, $date);
        $paysResults = $pays1943Repository->searchByNameAndDate($search, $date);

        return $this->mapResultsToDTO($communeResults, $paysResults);
    }

    /**
     * Convert results to DTO format.
     */
    /**
     * @param InseeCommune[]|InseeCommune1943[] $communeResults
     * @param InseePays[]|InseePays1943[]       $paysResults
     */
    private function mapResultsToDTO(array $communeResults, array $paysResults): array
    {
        $dtoResults = [];

        // Map communes
        foreach ($communeResults as $commune) {
            $dtoResults[] = $this->buildDto($commune);
        }

        // Map countries
        foreach ($paysResults as $pays) {
            $dtoResults[] = $this->buildDto($pays);
        }

        return $dtoResults;
    }

    private function buildDto(InseeCommune|InseeCommune1943|InseePays|InseePays1943 $entity): BirthPlaceDTO
    {
        if ($entity instanceof InseeCommune || $entity instanceof InseeCommune1943) {
            return new BirthPlaceDTO(
                label: $entity->getNomEnClairAvecArticle(),
                code: $entity->getCodeCommune(),
                type: 'city'
            );
        }

        return new BirthPlaceDTO(
            label: $entity->getLibelleCog(),
            code: $entity->getCodePays(),
            type: 'country'
        );
    }
}
