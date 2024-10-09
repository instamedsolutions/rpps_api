<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class RPPSFilter extends AbstractFilter
{
    use FilterTrait;

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
        array $context = []
    ): void {
        $this->queryNameGenerator = $queryNameGenerator;

        if (!array_key_exists($property, $this->properties)) {
            return;
        }

        if ('demo' === $property) {
            $value = self::parseBooleanValue($value);
            $this->addDemoFilter($queryBuilder, $value);
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

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.cityEntity", 'city', Join::WITH, 'city.canonical = :cityId');
        $queryBuilder->setParameter('cityId', $value);
    }

    public function addSpecialtyFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        if (!$value) {
            return $queryBuilder;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.specialtyEntity", 'specialty', Join::WITH, 'specialty.canonical = :specialtyId');
        $queryBuilder->setParameter('specialtyId', $value);

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

        $query = "(
        $alias.fullName LIKE CONCAT(:$paramName, '%') OR 
        $alias.fullNameInversed LIKE CONCAT(:$paramName, '%') OR
        $alias.idRpps = :$paramName
)";

        $queryBuilder->andWhere($query);
        $queryBuilder->setParameter($paramName, $value);

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
