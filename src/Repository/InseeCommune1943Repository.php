<?php

namespace App\Repository;

use App\Entity\InseeCommune1943;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseeCommune1943>
 *
 * @method InseeCommune1943|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseeCommune1943|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseeCommune1943[]    findAll()
 * @method InseeCommune1943[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseeCommune1943Repository extends ServiceEntityRepository
{
    use SearchNormalizationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommune1943::class);
    }

    public function searchByNameAndDate(string $search, DateTime $date): array
    {
        // Normalize both the database field and search term by removing spaces and hyphens
        // MySQL collations are typically accent-insensitive by default (utf8mb4_unicode_ci)
        $normalizedSearch = $this->normalizeSearchTerm($search);
        
        return $this->createQueryBuilder('c')
            ->where('REPLACE(REPLACE(LOWER(c.nomTypographie), \'-\', \'\'), \' \', \'\') LIKE LOWER(:search)')
            ->andWhere('(c.dateDebut IS NULL OR c.dateDebut <= :date)')
            ->andWhere('(c.dateFin IS NULL OR c.dateFin >= :date)')
            ->setParameter('search', "$normalizedSearch%")
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function findByCodeAndDate(string $code, DateTime $date): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.codeCommune = :code')
            ->andWhere('(c.dateDebut IS NULL OR c.dateDebut <= :date)')
            ->andWhere('(c.dateFin IS NULL OR c.dateFin >= :date)')
            ->setParameter('code', $code)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function findOneByCodeAndDate(string $code, DateTime $date): ?InseeCommune1943
    {
        return $this->createQueryBuilder('c')
            ->where('c.codeCommune = :code')
            ->andWhere('(c.dateDebut IS NULL OR c.dateDebut <= :date)')
            ->andWhere('(c.dateFin IS NULL OR c.dateFin >= :date)')
            ->setParameter('code', $code)
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
