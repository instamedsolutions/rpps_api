<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

/*
 *
 */
final class RPPSFilter extends AbstractContextAwareFilter
{


    /**
     * @var QueryNameGeneratorInterface
     */
    protected $queryNameGenerator;


    /**
     * @param string $property
     * @param $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     *
     * @throws \Exception
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->queryNameGenerator = $queryNameGenerator;

        if(!array_key_exists($property,$this->properties)) {
            return;
        }

        // Do not trigger if the value is empty
        if(!$value) {
            return;
        }

        $this->addSearchFilter($queryBuilder,$value);


    }


    /**
     * @param QueryBuilder $queryBuilder
     * @param string|null $value
     * @return QueryBuilder
     * @throws \Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder,?string $value) : QueryBuilder
    {


        $alias = $queryBuilder->getRootAliases()[0];

        // Generate a unique parameter name to avoid collisions with other filters
        $end = $this->queryNameGenerator->generateParameterName("search");

        $value = trim(preg_replace('#\s+#', ' ', $value));
        $value = str_replace(" ","%",$value);

        $query = "(
           CONCAT($alias.firstName,' ',$alias.lastName) LIKE :$end OR 
           CONCAT($alias.lastName,' ',$alias.firstName) LIKE :$end
           )";


        $queryBuilder->andWhere($query);

        $queryBuilder->setParameter($end,  "$value%");

        return $queryBuilder;
    }


    /**
     * @param string $resourceClass
     * @return array
     */
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
                'swagger' => array(
                    'description' => "Search by first name, last name...",
                    'type' => 'string',
                    'name' => $property,
                    'example' => "Jean Du",
                ),
            ];
        }

        return $description;
    }
}
