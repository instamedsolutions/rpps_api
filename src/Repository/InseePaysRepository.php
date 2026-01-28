<?php

namespace App\Repository;

use App\Entity\InseePays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseePays>
 *
 * @method InseePays|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseePays|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseePays[]    findAll()
 * @method InseePays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseePaysRepository extends ServiceEntityRepository
{
    use SearchNormalizationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseePays::class);
    }

    public function searchByName(string $search): array
    {
        // Normalize the search: remove accents, replace spaces/hyphens with SQL wildcards
        $normalizedSearch = $this->normalizeSearchTerm($search);
        
        return $this->createQueryBuilder('p')
            ->where('LOWER(REPLACE(REPLACE(p.libelleCog, \'-\', \'\'), \' \', \'\')) LIKE LOWER(:search)')
            ->setParameter('search', "%$normalizedSearch%")
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.codePays = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getResult();
    }
}
