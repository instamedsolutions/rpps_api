<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class SpecialtyFilter extends AbstractFilter
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
        array $context = [],
    ): void {
        $this->queryNameGenerator = $queryNameGenerator;

        if (!array_key_exists($property, $this->properties)) {
            return;
        }

        if ('is_paramedical' === $property) {
            $this->addParamedicalFilter($queryBuilder, $value);
        }

        if (!$value) {
            return;
        }

        if ('search' === $property) {
            $this->addSearchFilter($queryBuilder, $value);
        }

        if ('excluded_specialties' === $property) {
            $this->addExcludedSpecialtiesFilter($queryBuilder, $value);
        }

        if ('by_rpps' === $property) {
            $this->addSortByRppsCount($queryBuilder);
        }
    }

    protected function addParamedicalFilter(QueryBuilder $queryBuilder, mixed $value): QueryBuilder
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if (null === $value) {
            return $queryBuilder;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere("$rootAlias.isParamedical = :isParamedical");
        $queryBuilder->setParameter('isParamedical', $value);

        return $queryBuilder;
    }

    protected function addExcludedSpecialtiesFilter(QueryBuilder $queryBuilder, string|array $value): QueryBuilder
    {
        $value = is_string($value) ? [$value] : $value;

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$rootAlias.canonical NOT IN (:excludedSpecialties)");
        $queryBuilder->setParameter('excludedSpecialties', $value);

        return $queryBuilder;
    }

    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        $value = $this->cleanValue($value);

        $query = "(
        $alias.canonical = :searchValue OR 
        $alias.id = :searchValue OR
        $alias.name LIKE CONCAT('%', :searchValue, '%') OR
        $alias.specialistName LIKE CONCAT('%', :searchValue, '%')
        )";

        $queryBuilder->andWhere($query);
        $queryBuilder->setParameter('searchValue', $value);

        return $queryBuilder;
    }

    protected function addSortByRppsCount(QueryBuilder $queryBuilder): void
    {
        // sort by main and name
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->addOrderBy("$rootAlias.main", 'DESC');
        $queryBuilder->addOrderBy("$rootAlias.name", 'ASC');
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Search by exact canonical name or ID, or partial match for name or specialist name.',
                    'type' => 'string',
                    'name' => 'search',
                    'example' => 'cardio',
                ],
            ],
            'by_rpps' => [
                'property' => 'by_rpps',
                'type' => 'boolean',
                'required' => false,
                'swagger' => [
                    'description' => 'Sort specialties by the number of associated RPPS users (doctors).',
                    'type' => 'boolean',
                    'name' => 'by_rpps',
                    'example' => 'true',
                ],
            ],
        ];
    }
}
