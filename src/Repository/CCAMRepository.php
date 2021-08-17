<?php

namespace App\Repository;

use App\Entity\CCAM;
use App\Entity\CCAMGroup;
use App\Entity\DiseaseGroup;
use App\Entity\Drug;
use App\Entity\RPPS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CCAM|null findOneBy(array $criteria, array $orderBy = null)
 * @method CCAM[]    findAll()
 * @method CCAM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CCAMRepository extends ServiceEntityRepository
{


    /**
     * RPPSRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CCAM::class);
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return Drug|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {

        if(null === $id || 0 === $id) {
            return null;
        }

        return $this->createQueryBuilder('d')
            ->where('d.id = :id')
            ->orWhere('d.code = :id')
            ->setParameter('id',$id)
            ->getQuery()
            ->getOneOrNullResult();
    }




}
