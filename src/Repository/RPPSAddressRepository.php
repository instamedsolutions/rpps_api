<?php

namespace App\Repository;

use App\Entity\RPPSAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RPPSAddress>
 *
 * @method RPPSAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method RPPSAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method RPPSAddress[]    findAll()
 * @method RPPSAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RPPSAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RPPSAddress::class);
    }
}
