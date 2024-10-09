<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class RPPSFilter extends AbstractContextAwareFilter
{
    use FilterTrait;

    protected ?QueryNameGeneratorInterface $queryNameGenerator = null;

    /**
     * @throws Exception
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $this->queryNameGenerator = $queryNameGenerator;

        if (!array_key_exists($property, $this->properties)) {
            return;
        }

        if ('demo' === $property) {
            $value = self::parseBooleanValue($value);
            $this->addDemoFilter($queryBuilder, $value);
        }

        // Do not trigger if the value is empty
        if (!$value) {
            return;
        }

        if ('search' === $property) {
            $this->addSearchFilter($queryBuilder, $value);
        }
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
