<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\City;
use App\Entity\Specialty;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class RPPSFilter extends AbstractFilter
{
    use FilterTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        ?ManagerRegistry $managerRegistry,
        private readonly RequestStack $requestStack,
        ?LoggerInterface $logger = null,
        protected ?array $properties = null,
        protected ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected ?QueryNameGeneratorInterface $queryNameGenerator = null;

    /**
     * @throws Exception
     */
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->queryNameGenerator = $queryNameGenerator;

        if (!array_key_exists($property, $this->properties)) {
            return;
        }

        if ('demo' === $property) {
            $value = self::parseBooleanValue($value);
            $this->addDemoFilter($queryBuilder, $value);
        }

        if ('latitude' === $property) {
            $this->addLatitudeFilter($queryBuilder, $value, $operation);
        }

        if (!$value) {
            return;
        }

        if ('specialty' === $property) {
            $this->addSpecialtyFilter($queryBuilder, $value);
        }

        if ('city' === $property) {
            $this->addCityFilter($queryBuilder, $value);
        }

        if ('first_letter' === $property) {
            $this->addFirstLetterFilter($queryBuilder, $value);
        }

        if ('search' === $property) {
            $this->addSearchFilter($queryBuilder, $value);
        }

        if ('excluded_rpps' === $property) {
            $this->addExcludedRppsFilter($queryBuilder, $value);
        }
    }

    protected function addCityFilter(QueryBuilder $queryBuilder, ?string $value): void
    {
        if (!$value) {
            return;
        }

        /** @var City|null $city */
        $city = $this->em->getRepository(City::class)->findOneBy(['canonical' => $value]);

        if (!$city) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($city->getSubCities()->toArray()) {
            $queryBuilder->innerJoin("$rootAlias.cityEntity", 'city', Join::WITH, 'city.canonical IN (:cityId)');
            $queryBuilder->setParameter('cityId', [
                $value,
                ...array_map(fn (City $city) => $city->getCanonical(), $city->getSubCities()->toArray()),
            ]);
        } else {
            $queryBuilder->innerJoin("$rootAlias.cityEntity", 'city', Join::WITH, 'city.canonical = :cityId');
            $queryBuilder->setParameter('cityId', $value);
        }
    }

    public function addSpecialtyFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        if (!$value) {
            return $queryBuilder;
        }

        $specialty = $this->em->getRepository(Specialty::class)->findOneBy(['canonical' => $value]);

        if (!$specialty) {
            $queryBuilder->andWhere('1 = 2');

            return $queryBuilder;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$rootAlias.specialtyEntity = :specialty");
        $queryBuilder->setParameter('specialty', $specialty->getId());

        return $queryBuilder;
    }

    protected function addFirstLetterFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        if (!$value) {
            return $queryBuilder;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$rootAlias.lastName LIKE :firstLetter");
        $queryBuilder->setParameter('firstLetter', $value . '%');

        return $queryBuilder;
    }

    /**
     * @throws Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        // Generate a unique parameter name to avoid collisions with other filters
        $paramName = $this->queryNameGenerator->generateParameterName('search');

        $value = $this->cleanValue($value);

        if (str_contains($value, '%')) {
            $result = $this->em->getConnection()->fetchFirstColumn('(SELECT id FROM rpps WHERE full_name LIKE :search
 LIMIT 500)
UNION
(SELECT id FROM rpps WHERE full_name_inversed LIKE :search
 LIMIT 500)
LIMIT 500;', [
                'search' => "$value%",
            ]);

            $queryBuilder->andWhere("$alias.id IN (:result)");
            $queryBuilder->setParameter('result', $result);
        } else {
            $query = "(
        $alias.fullName LIKE CONCAT(:$paramName, '%') OR 
        $alias.fullNameInversed LIKE CONCAT(:$paramName, '%') OR
        $alias.idRpps = :$paramName
)";

            $queryBuilder->andWhere($query);
            $queryBuilder->setParameter($paramName, $value);
        }

        return $queryBuilder;
    }

    protected function addExcludedRppsFilter(QueryBuilder $queryBuilder, mixed $excludedRpps): QueryBuilder
    {
        if (!is_array($excludedRpps)) {
            $excludedRpps = [$excludedRpps];
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$alias.idRpps NOT IN (:excludedRpps)")
            ->setParameter('excludedRpps', $excludedRpps);

        return $queryBuilder;
    }

    public function addLatitudeFilter(QueryBuilder $queryBuilder, ?string $latitude, ?Operation &$operation): QueryBuilder
    {
        $request = $this->requestStack->getCurrentRequest();
        $longitude = $request?->query->get('longitude');

        if (!$latitude || !$longitude) {
            return $queryBuilder;
        }
        $operation = $operation->withPaginationClientEnabled(false);
        $operation = $operation->withPaginationClientPartial(true);

        $request->attributes->set('_api_operation', $operation);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Convert 30,000 meters to degrees
        $distance = 30000; // 30km
        $earthRadius = 111000; // meters per degree latitude

        // Calculate latitude and longitude offsets in degrees
        $latOffset = $distance / $earthRadius; // ~0.27027 degrees for 30km
        $lngOffset = $distance / ($earthRadius * cos(deg2rad((float) $latitude))); // Longitude offset

        // Calculate min/max latitudes and longitudes
        $minLat = (float) $latitude - (float) $latOffset;
        $maxLat = (float) $latitude + (float) $latOffset;
        $minLng = (float) $longitude - (float) $lngOffset;
        $maxLng = (float) $longitude + (float) $lngOffset;

        // Add bounding box condition using the POINT function
        $queryBuilder->andWhere(
            'MBRContains(ST_MakeEnvelope(POINT(:minLng, :minLat), POINT(:maxLng, :maxLat)), ' . $rootAlias . '.coordinates) = true'
        );

        // Apply the more accurate distance filter
        $queryBuilder->andWhere(
            "ST_Distance_Sphere(POINT(:longitude, :latitude), $rootAlias.coordinates) < :distance"
        )
            ->addSelect(
                "ST_Distance_Sphere(POINT(:longitude, :latitude), $rootAlias.coordinates) AS HIDDEN distance"
            );

        // Set parameters
        $queryBuilder->setParameter('latitude', (float) $latitude);
        $queryBuilder->setParameter('longitude', (float) $longitude);
        $queryBuilder->setParameter('distance', (float) $distance);
        $queryBuilder->setParameter('minLat', $minLat);
        $queryBuilder->setParameter('maxLat', $maxLat);
        $queryBuilder->setParameter('minLng', $minLng);
        $queryBuilder->setParameter('maxLng', $maxLng);

        //    $queryBuilder->orderBy(
        //        'distance',
        //        'ASC'
        //    );

        return $queryBuilder;
    }

    public function addDemoFilter(QueryBuilder $queryBuilder, ?bool $value): QueryBuilder
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (null === $value) {
            return $queryBuilder;
        }

        if ($value) {
            $queryBuilder->andWhere("$rootAlias.idRpps LIKE :start");
        } else {
            $queryBuilder->andWhere("$rootAlias.idRpps NOT LIKE :start");
        }

        $queryBuilder->setParameter('start', '2%');

        return $queryBuilder;
    }

    public static function parseBooleanValue(string $string): ?bool
    {
        $string = trim(strtolower($string));

        // If true or 1, returns true
        // if false or 0 returns false
        // Else, incorrect value : returns null
        return in_array($string, ['1', 'true']) ? true : (in_array($string, ['0', 'false']) ? false : null);
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            if ('search' === $property) {
                $description[$property] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'swagger' => [
                        'description' => 'Search by first name, last name, RPPS number...',
                        'type' => 'string',
                        'name' => $property,
                        'example' => 'Jean Du',
                    ],
                ];
            } elseif ('demo' === $property) {
                $description[$property] = [
                    'property' => $property,
                    'type' => 'boolean',
                    'required' => false,
                    'swagger' => [
                        'description' => 'Filter by demo flag (true or false)',
                        'type' => 'boolean',
                        'name' => $property,
                        'example' => 'true',
                    ],
                ];
            } elseif ('excluded_rpps' === $property) {
                $description[$property] = [
                    'property' => $property,
                    'type' => 'array',
                    'required' => false,
                    'swagger' => [
                        'description' => 'Exclude specific RPPS numbers from the result set. Provide one or more RPPS numbers.',
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'example' => '12222222222',
                        ],
                        'name' => $property,
                        'example' => ['12222222222', '13333333333'],
                    ],
                ];
            }
        }

        return $description;
    }
}
