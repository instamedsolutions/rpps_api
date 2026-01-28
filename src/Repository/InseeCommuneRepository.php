<?php

namespace App\Repository;

use App\Entity\InseeCommune;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseeCommune>
 *
 * @method InseeCommune|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseeCommune|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseeCommune[]    findAll()
 * @method InseeCommune[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseeCommuneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommune::class);
    }

    public function searchByName(string $search): array
    {
        // Normalize the search: remove accents, replace spaces/hyphens with SQL wildcards
        $normalizedSearch = $this->normalizeSearchTerm($search);
        
        return $this->createQueryBuilder('c')
            ->where('LOWER(REPLACE(REPLACE(c.nomEnClair, \'-\', \'\'), \' \', \'\')) LIKE LOWER(:search)')
            ->setParameter('search', "$normalizedSearch%")
            ->getQuery()
            ->getResult();
    }

    /**
     * Normalize search term by removing accents, spaces, and hyphens
     */
    private function normalizeSearchTerm(string $search): string
    {
        // Remove accents
        $search = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $search);
        // Remove spaces and hyphens
        $search = str_replace([' ', '-'], '', $search);
        
        return $search;
    }

    public function findByCode(string $code): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.codeCommune = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();
    }
}
