<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use App\Entity\CCAM;
use App\Entity\CCAMGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CCAMGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CCAMGroup[]    findAll()
 * @method CCAMGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CCAMGroupRepository extends ServiceEntityRepository
{


    /**
     * RPPSRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CCAMGroup::class);
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return CCAM|null
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?CCAMGroup
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
