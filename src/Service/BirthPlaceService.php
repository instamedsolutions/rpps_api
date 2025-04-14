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

    /**
     * Search for birthplaces filtered by date.
     */
    public function searchBirthPlacesByDate(string $search, DateTime $date): array
    {
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
    private function mapResultsToDTO(array $communeResults, array $paysResults): array
    {
        $dtoResults = [];

        // Map communes
        foreach ($communeResults as $commune) {
            $dtoResults[] = new BirthPlaceDTO(
                label: $commune->getNomEnClairAvecArticle(),
                code: $commune->getCodeCommune(),
                type: 'city'
            );
        }

        // Map countries
        foreach ($paysResults as $pays) {
            $dtoResults[] = new BirthPlaceDTO(
                label: $pays->getLibelleCog(),
                code: $pays->getCodePays(),
                type: 'country'
            );
        }

        return $dtoResults;
    }
}
