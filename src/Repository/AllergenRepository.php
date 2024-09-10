<?php

namespace App\Repository;

use App\Entity\Allergen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Allergen|null findOneBy(array $criteria, array $orderBy = null)
 * @method Allergen[]    findAll()
 * @method Allergen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllergenRepository extends ServiceEntityRepository
{
    /**
     * RPPSRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Allergen::class);
    }

    /**
     * @param null $lockMode
     * @param null $lockVersion
     *
     * @return Allergen|null
     *
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null)
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
