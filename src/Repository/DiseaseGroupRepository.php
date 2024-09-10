<?php

namespace App\Repository;

use App\Entity\DiseaseGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiseaseGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiseaseGroup[]    findAll()
 * @method DiseaseGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiseaseGroupRepository extends ServiceEntityRepository
{
    /**
     * RPPSRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseGroup::class);
    }

    /**
     * @param null $lockMode
     * @param null $lockVersion
     *
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?DiseaseGroup
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
