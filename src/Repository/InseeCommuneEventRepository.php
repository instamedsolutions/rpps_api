<?php

namespace App\Repository;

use App\Entity\InseeCommuneEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseeCommuneEvent>
 *
 * @method InseeCommuneEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseeCommuneEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseeCommuneEvent[]    findAll()
 * @method InseeCommuneEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseeCommuneEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseeCommuneEvent::class);
    }
}
