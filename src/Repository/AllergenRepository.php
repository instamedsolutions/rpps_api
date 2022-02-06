<?php

namespace App\Repository;

use App\Entity\Allergen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\All;

/**
 * @method Allergen|null findOneBy(array $criteria, array $orderBy = null)
 * @method Allergen[]    findAll()
 * @method Allergen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllergenRepository extends ServiceEntityRepository
{


    /**
     * RPPSRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Allergen::class);
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return Allergen|null
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
