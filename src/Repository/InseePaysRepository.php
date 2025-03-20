<?php

namespace App\Repository;

use App\Entity\InseePays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InseePays>
 *
 * @method InseePays|null find($id, $lockMode = null, $lockVersion = null)
 * @method InseePays|null findOneBy(array $criteria, array $orderBy = null)
 * @method InseePays[]    findAll()
 * @method InseePays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InseePaysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InseePays::class);
    }

    public function searchByName(string $search): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.libelleCog LIKE :search')
            ->setParameter('search', "%$search%")
            ->getQuery()
            ->getResult();
    }
}
