<?php

namespace App\ApiPlatform\Filter;

use Exception;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;


final class NGAPFilter extends AbstractContextAwareFilter
{

    use FilterTrait;

    protected ?QueryNameGeneratorInterface $queryNameGenerator = null;


    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->queryNameGenerator = $queryNameGenerator;

        if (!array_key_exists($property, $this->properties)) {
            return;
        }

        // Do not trigger if the value is empty
        if (!$value) {
            return;
        }

        $this->addSearchFilter($queryBuilder, $value);
    }


    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        $value = trim($value);


        // Generate a unique parameter name to avoid collisions with other filters
        $start = $this->queryNameGenerator->generateParameterName("search");
        $full = $this->queryNameGenerator->generateParameterName("search");

        $queryBuilder->andWhere("$alias.description LIKE :$full OR $alias.code LIKE :$start");

        $queryBuilder->setParameter($full, "%$value%");
        $queryBuilder->setParameter($start, "$value%");

        return $queryBuilder;
    }


    public function getDescription(string $resourceClass): array
    {
        $description = [];

        if (!$this->properties) {
            return $description;
        }


        if (isset($this->properties['search'])) {
            $description["search"] = [
                'property' => "search",
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => "Search by code or description...",
                    'type' => 'string',
                    'name' => "search",
                    'example' => "Acte de kinésithérapie"
                ],
            ];
        }


        return $description;
    }
}
