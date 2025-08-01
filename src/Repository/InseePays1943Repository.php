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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseePays1943::class);
    }

    /**
     * Note : might consider searching also the libelleOfficiel field.
     *
     * Search for countries matching a name that existed at a given date.
     */
    public function searchByNameAndDate(string $search, DateTime $date): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.libelleCog LIKE :search')
            ->andWhere('(p.dateDebut IS NULL OR p.dateDebut <= :date)')
            ->andWhere('(p.dateFin IS NULL OR p.dateFin >= :date)')
            ->setParameter('search', "%$search%")
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
