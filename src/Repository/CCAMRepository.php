<?php

namespace App\Repository;

use App\Entity\CCAM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CCAM|null findOneBy(array $criteria, array $orderBy = null)
 * @method CCAM[]    findAll()
 * @method CCAM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CCAMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CCAM::class);
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?CCAM
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
