<?php

namespace App\Repository;

use App\Entity\Cim11;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cim11|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cim11[]    findAll()
 * @method Cim11[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Cim11Repository extends ServiceEntityRepository
{
    /**
     * RPPSRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cim11::class);
    }

    /**
     * @param null $lockMode
     * @param null $lockVersion
     *
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?Cim11
    {
        if (null === $id || 0 === $id) {
            return null;
        }

        if (strlen($id) < 10) {
            $id = str_replace('-', '.', $id);
        }

        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->orWhere('r.code = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
