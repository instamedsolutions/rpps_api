<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class CityFilter extends AbstractFilter
{
    use FilterTrait;

    protected ?QueryNameGeneratorInterface $queryNameGenerator = null;

    public function __construct(
        protected ManagerRegistry $managerRegistry,
        private readonly RequestStack $requestStack,
        ?LoggerInterface $logger = null,
        protected ?array $properties = null,
        protected ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($this->managerRegistry, $logger, $properties, $nameConverter);
    }

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

        // Do not trigger if the value is empty
        if (!$value) {
            return;
        }

        if ('latitude' === $property) {
            $this->addLatitudeFilter($queryBuilder, $value);

            return;
        }

        $this->addSearchFilter($queryBuilder, $value);
    }

    public function addLatitudeFilter(QueryBuilder $queryBuilder, ?string $latitude): QueryBuilder
    {
        $request = $this->requestStack->getCurrentRequest();
        $longitude = $request?->query->get('longitude');

        if (!$latitude || !$longitude) {
            return $queryBuilder;
        }

        // Distance in km
        $distance = $request->query->get('distance') ?? 30;

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Apply the more accurate distance filter
        $queryBuilder->andWhere(
            'ST_Distance_Sphere(POINT(:longitude, :latitude), ' . $rootAlias . '.coordinates) < :distance'
        );
        // Set parameters
        $queryBuilder->setParameter('latitude', (float) $latitude);
        $queryBuilder->setParameter('longitude', (float) $longitude);
        $queryBuilder->setParameter('distance', (float) $distance * 1000);

        $queryBuilder->orderBy(
            'ST_Distance_Sphere(POINT(:longitude, :latitude), ' . $rootAlias . '.coordinates)',
            'ASC'
        );

        return $queryBuilder;
    }

    /**
     * @throws Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        // Generate a unique parameter name to avoid collisions with other filters
        $end = $this->queryNameGenerator->generateParameterName('search');

        $queryBuilder->andWhere("$alias.name LIKE :$end OR $alias.rawName LIKE :$end OR $alias.postalCode = :value");

        $value = $this->cleanValue($value, false);

        $queryBuilder->setParameter($end, "$value%");
        $queryBuilder->setParameter('value', $value);

        return $queryBuilder;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Search by first name, last name...',
                    'type' => 'string',
                    'name' => $property,
                    'example' => 'Jean Du',
                ],
            ];
        }

        return $description;
    }
}
