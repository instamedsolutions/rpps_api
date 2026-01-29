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
        // Normalize hyphens to spaces at the SQL level for flexible matching
        // MySQL collations are typically accent-insensitive by default (utf8mb4_unicode_ci)
        return $this->createQueryBuilder('p')
            ->where('REPLACE(p.libelleCog, \'-\', \' \') LIKE :search')
            ->setParameter('search', '%' . str_replace('-', ' ', $search) . '%')
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
