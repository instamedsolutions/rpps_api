<?php

namespace App\Repository;

use App\Entity\InseePays1943;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseePays1943>
 *
 * @method InseePays1943|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseePays1943|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseePays1943[]    findAll()
 * @method InseePays1943[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseePays1943Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseePays1943::class);
    }
}
