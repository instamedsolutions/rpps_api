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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommune1943::class);
    }

    public function searchByNameAndDate(string $search, DateTime $date): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.nomTypographie LIKE :search')
            ->andWhere('(c.dateDebut IS NULL OR c.dateDebut <= :date)')
            ->andWhere('(c.dateFin IS NULL OR c.dateFin >= :date)')
            ->setParameter('search', "%$search%")
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
}
