<?php

namespace App\Repository;

use App\Entity\InseeCommune1943;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseeCommune1943>
 *
 * @method InseeCommune1943|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseeCommune1943|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseeCommune1943[]    findAll()
 * @method InseeCommune1943[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseeCommune1943Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommune1943::class);
    }
}
