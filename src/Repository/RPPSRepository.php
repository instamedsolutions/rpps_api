<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use App\Entity\DocumentType;
use App\Entity\RPPS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RPPS|null findOneBy(array $criteria, array $orderBy = null)
 * @method RPPS[]    findAll()
 * @method RPPS[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RPPSRepository extends ServiceEntityRepository
{


    /**
     * RPPSRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RPPS::class);
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?RPPS
    {
        if (null === $id || 0 === $id) {
            return null;
        }

        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->orWhere('r.idRpps = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
