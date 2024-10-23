<?php

namespace App\Repository;

use App\Entity\Specialty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Specialty>
 *
 * @method Specialty|null findOneBy(array $criteria, array $orderBy = null)
 * @method Specialty[]    findAll()
 * @method Specialty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpecialtyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Specialty::class);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?Specialty
    {
        if (null === $id || 0 === $id) {
            return null;
        }

        return $this->createQueryBuilder('d')
            ->where('d.id = :id')
            ->orWhere('d.canonical = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
