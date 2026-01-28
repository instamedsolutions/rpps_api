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
        // Normalize both the database field and search term by removing spaces and hyphens
        // MySQL collations are typically accent-insensitive by default (utf8mb4_unicode_ci)
        $normalizedSearch = $this->normalizeSearchTerm($search);
        
        return $this->createQueryBuilder('c')
            ->where('REPLACE(REPLACE(LOWER(c.nomEnClair), \'-\', \'\'), \' \', \'\') LIKE LOWER(:search)')
            ->setParameter('search', "$normalizedSearch%")
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
