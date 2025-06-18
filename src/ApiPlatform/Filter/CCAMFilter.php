<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\BaseEntity;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class CCAMFilter extends AbstractFilter
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

        // Do not trigger if the value is empty
        if (!$value) {
            return;
        }

        if ('search' === $property) {
            $this->addSearchFilter($queryBuilder, $value);
        }

        if ('id' === $property) {
            $this->addIdSearchFilter($queryBuilder, $value);
        }
    }

    /**
     * @throws Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        // $value = str_replace(" ","%",trim($value));
        $value = trim($value);

        // Generate a unique parameter name to avoid collisions with other filters
        $start = $this->queryNameGenerator->generateParameterName('search');
        $full = $this->queryNameGenerator->generateParameterName('search');

        $queryBuilder->andWhere("$alias.name LIKE :$full OR $alias.code LIKE :$start");

        $queryBuilder->setParameter($full, "%$value%");
        $queryBuilder->setParameter($start, "$value%");

        return $queryBuilder;
    }

    protected function addIdSearchFilter(QueryBuilder $queryBuilder, string|array|null $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (is_array($value)) {
            $value = array_map([BaseEntity::class, 'parseId'], $value);
            $queryBuilder->andWhere("$alias.id IN (:ids) OR $alias.code IN (:ids)");
            $queryBuilder->setParameter('ids', $value);
        } else {
            $queryBuilder->andWhere("$alias.id = :id or $alias.code = :id");
            $queryBuilder->setParameter('id', BaseEntity::parseId($value));
        }

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
                    'description' => 'Search by code or name...',
                    'type' => 'string',
                    'name' => $property,
                    'example' => 'Jean Du',
                ],
            ];
        }

        return $description;
    }
}
