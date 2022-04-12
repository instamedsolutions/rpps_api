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

    use FilterTrait;


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


        if($property === "demo") {
            $value = self::parseBooleanValue($value);
            $this->addDemoFilter($queryBuilder, $value);
        }


        // Do not trigger if the value is empty
        if(!$value) {
            return;
        }

        if($property === "search") {
            $this->addSearchFilter($queryBuilder, $value);
        }

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

        $value = $this->cleanValue($value);

        $query = "(
           CONCAT($alias.firstName,' ',$alias.lastName) LIKE :$end OR 
           CONCAT($alias.lastName,' ',$alias.firstName) LIKE :$end
           )";


        $queryBuilder->andWhere($query);

        $queryBuilder->setParameter($end,  "$value%");

        return $queryBuilder;
    }


    /**
     * @param QueryBuilder $queryBuilder
     * @param bool|null $value
     * @return QueryBuilder
     */
    public function addDemoFilter(QueryBuilder $queryBuilder,?bool $value) : QueryBuilder
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if(null === $value) {
            return $queryBuilder;
        }

        if($value) {
            $queryBuilder->andWhere("$rootAlias.idRpps LIKE :start");
        } else {
            $queryBuilder->andWhere("$rootAlias.idRpps NOT LIKE :start");
        }

        $queryBuilder->setParameter("start","2%");

        return $queryBuilder;
    }



    /**
     * @param string $string
     * @return bool|null
     */
    public static function parseBooleanValue(string $string) : ?bool
    {

        $string = trim(strtolower($string));

        // If true or 1, returns true
        // if false or 0 returns false
        // Else, incorrect value : returns null
        return in_array($string,["1","true"]) ? true : (in_array($string,["0","false"]) ? false : null);

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
