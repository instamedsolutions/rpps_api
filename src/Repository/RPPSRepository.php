<?php

namespace App\Repository;

use App\Entity\RPPS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
     * @param null $lockMode
     * @param null $lockVersion
     *
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

    /**
     * Retourne le nombre de médecins associés à une spécialité donnée.
     */
    public function getNbRppsForSpecialty(string $specialtyId): int
    {
        // Création du QueryBuilder
        $queryBuilder = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.specialtyEntity = :specialtyId')
            ->setParameter('specialtyId', $specialtyId);

        // Exécution de la requête et retour du résultat sous forme de nombre entier
        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }
}
