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
        array $context = []
    ): void {
        $this->queryNameGenerator = $queryNameGenerator;

        if (!array_key_exists($property, $this->properties)) {
            return;
        }

        if (!$value) {
            return;
        }

        if ('search' === $property) {
            $this->addSearchFilter($queryBuilder, $value);
        }
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
        ];
    }
}
