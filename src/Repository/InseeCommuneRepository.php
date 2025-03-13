<?php

namespace App\Repository;

use App\Entity\InseeCommune;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseeCommune>
 *
 * @method InseeCommune|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseeCommune|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseeCommune[]    findAll()
 * @method InseeCommune[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseeCommuneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommune::class);
    }
}
