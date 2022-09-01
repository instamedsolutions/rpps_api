<?php

namespace App\ApiPlatform\Filter;

use Exception;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/*
 *
 */

final class DiseaseGroupFilter extends AbstractContextAwareFilter
{

    use FilterTrait;

    protected ?QueryNameGeneratorInterface $queryNameGenerator = null;


    /**
     * @param $value
     * @param string|null $operationName
     *
     * @throws Exception
     */
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


    /**
     * @throws Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        // Generate a unique parameter name to avoid collisions with other filters
        $start = $this->queryNameGenerator->generateParameterName("search");
        $full = $this->queryNameGenerator->generateParameterName("search");

        $queryBuilder->andWhere("$alias.name LIKE :$full OR $alias.cim LIKE :$start");

        $value = $this->cleanValue($value);

        $queryBuilder->setParameter($full, "%$value%");
        $queryBuilder->setParameter($start, "$value%");

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
                    'description' => "Search by first name, last name...",
                    'type' => 'string',
                    'name' => $property,
                    'example' => "Jean Du"
                ],
            ];
        }

        return $description;
    }
}
