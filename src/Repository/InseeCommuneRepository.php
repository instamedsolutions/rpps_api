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
    use SearchNormalizationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommune::class);
    }

    public function searchByName(string $search): array
    {
        // Normalize hyphens to spaces at the SQL level for flexible matching
        // MySQL collations are typically accent-insensitive by default (utf8mb4_unicode_ci)
        return $this->createQueryBuilder('c')
            ->where('REPLACE(c.nomEnClair, \'-\', \' \') LIKE :search')
            ->setParameter('search', '%' . str_replace('-', ' ', $search) . '%')
            ->getQuery()
            ->getResult();
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
