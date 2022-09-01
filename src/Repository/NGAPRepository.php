<?php

namespace App\Repository;

use App\Entity\NGAP;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NGAP|null findOneBy(array $criteria, array $orderBy = null)
 * @method NGAP[]    findAll()
 * @method NGAP[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NGAPRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NGAP::class);
    }


    public function find($id, $lockMode = null, $lockVersion = null): ?NGAP
    {
        if (null === $id || 0 === $id) {
            return null;
        }

        return $this->createQueryBuilder('d')
            ->where('d.id = :id')
            ->orWhere('d.code = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
