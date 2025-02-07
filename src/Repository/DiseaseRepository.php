<?php

namespace App\Repository;

use App\Entity\Disease;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Disease|null findOneBy(array $criteria, array $orderBy = null)
 * @method Disease[]    findAll()
 * @method Disease[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiseaseRepository extends ServiceEntityRepository
{
    /**
     * RPPSRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Disease::class);
    }

    /**
     * @param null $lockMode
     * @param null $lockVersion
     *
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Disease
    {
        if (null === $id || 0 === $id) {
            return null;
        }

        return $this->createQueryBuilder('d')
            ->where('d.id = :id')
            ->orWhere('d.cim = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
