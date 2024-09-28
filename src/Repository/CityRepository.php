<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 *
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * Finds a sub-city of the given city that has latitude and longitude coordinates.
     * Returns one such sub-city, or null if none is found.
     *
     * @throws NonUniqueResultException
     */
    public function findSubCityWithCoordinates(City $city): ?City
    {
        return $this->createQueryBuilder('c')
            ->where('c.mainCity = :city')
            ->andWhere('c.latitude IS NOT NULL')
            ->andWhere('c.longitude IS NOT NULL')
            ->setParameter('city', $city)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Finds similar cities in the same department as the given city.
     * Excludes the current city from the result and limits the result to 10 cities.
     *
     * @return City[]
     */
    public function findSimilarCitiesInDepartment(City $city): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.department = :department')
            ->andWhere('c.id != :cityId')
            ->setParameter('department', $city->getDepartment())
            ->setParameter('cityId', $city->getId())
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds cities similar to the given city based on geographical proximity (within a 50km radius).
     * Calculates distance using the Haversine formula, sorts cities by distance, and limits the result to 10 cities.
     * Returns an array of City entities in the correct order by proximity.
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     *
     * @return City[]
     */
    public function findSimilarCitiesByCoordinates(City $city): array
    {
        $latitude = $city->getLatitude();
        $longitude = $city->getLongitude();
        $regionId = $city->getDepartment()->getRegion()->getId();

        // Check if we have valid coordinates
        if ($latitude === null || $longitude === null) {
            return [];
        }

        // Native SQL to find similar cities within the same region based on geographical distance
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT c.id,
        (6371 * ACOS(COS(RADIANS(:latitude)) * COS(RADIANS(c.latitude)) * COS(RADIANS(c.longitude) - RADIANS(:longitude)) + SIN(RADIANS(:latitude)) * SIN(RADIANS(c.latitude)))) AS distance
        FROM city c
        INNER JOIN department d ON c.department_id = d.id
        INNER JOIN region r ON d.region_id = r.id
        WHERE r.id = :regionId
        AND c.latitude IS NOT NULL
        AND c.longitude IS NOT NULL
        AND c.population > :population
        AND c.id != :cityId
        ORDER BY distance
    ';

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'regionId' => $regionId,
            'population' => 1000,
            'cityId' => $city->getId(),
        ]);

        $results = $stmt->fetchAllAssociative();

        if (!$results) {
            return [];
        }

        // Limit to the closest 10 cities
        $closestCities = array_slice($results, 0, 10);

        // Fetch City entities for the results
        $cityIds = array_column($closestCities, 'id');
        return $this->createQueryBuilder('c')
            ->where('c.id IN (:cityIds)')
            ->setParameter('cityIds', $cityIds)
            ->getQuery()
            ->getResult();
    }
}
