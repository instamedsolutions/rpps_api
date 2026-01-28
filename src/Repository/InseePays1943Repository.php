<?php

namespace App\Repository;

use App\Entity\InseePays1943;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseePays1943>
 *
 * @method InseePays1943|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseePays1943|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseePays1943[]    findAll()
 * @method InseePays1943[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseePays1943Repository extends ServiceEntityRepository
{
    use SearchNormalizationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseePays1943::class);
    }

    /**
     * Note : might consider searching also the libelleOfficiel field.
     *
     * Search for countries matching a name.
     * Date constraints removed to allow searching for historical countries (e.g., Algeria before 1962).
     */
    public function searchByNameAndDate(string $search, DateTime $date): array
    {
        // Normalize both the database field and search term by removing spaces and hyphens
        // MySQL collations are typically accent-insensitive by default (utf8mb4_unicode_ci)
        $normalizedSearch = $this->normalizeSearchTerm($search);
        
        return $this->createQueryBuilder('p')
            ->where('REPLACE(REPLACE(LOWER(p.libelleCog), \'-\', \'\'), \' \', \'\') LIKE LOWER(:search)')
            // Date constraints removed to allow historical country searches (e.g., Algeria before 1962)
            ->setParameter('search', "%$normalizedSearch%")
            ->getQuery()
            ->getResult();
    }

    public function findByCodeAndDate(string $code, DateTime $date): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.codePays = :code')
            // Keep date constraints for find by code
            ->andWhere('(p.dateDebut IS NULL OR p.dateDebut <= :date)')
            ->andWhere('(p.dateFin IS NULL OR p.dateFin >= :date)')
            ->setParameter('code', $code)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function findOneByCodeAndDate(string $code, DateTime $date): ?InseePays1943
    {
        return $this->createQueryBuilder('p')
            ->where('p.codePays = :code')
            ->andWhere('(p.dateDebut IS NULL OR p.dateDebut <= :date)')
            ->andWhere('(p.dateFin IS NULL OR p.dateFin >= :date)')
            ->setParameter('code', $code)
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
