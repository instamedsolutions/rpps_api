<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Cim11;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class Cim11Filter extends AbstractFilter
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
            $this->addSearchFilter($queryBuilder, $value, $context['languages'] ?? []);

            return;
        }

        if ('ids' === $property) {
            $this->addIdsFilter($queryBuilder, $value);
        }
    }

    protected function addIdsFilter(QueryBuilder $queryBuilder, string|array|null $value): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (!$value) {
            return $queryBuilder;
        }

        $ids = is_string($value) ? explode(',', $value) : $value;

        $queryBuilder->andWhere("$alias.id IN (:ids) OR $alias.code IN (:ids)");
        $queryBuilder->setParameter('ids', $ids);

        return $queryBuilder;
    }

    /**
     * @throws Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value, array $languages = []): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        $value = str_replace(' ', '%', trim($value));

        // Generate a unique parameter name to avoid collisions with other filters
        $start = $this->queryNameGenerator->generateParameterName('search');
        $full = $this->queryNameGenerator->generateParameterName('search');

        $exact = $this->queryNameGenerator->generateParameterName('exact');

        $defaultLanguage = (new Cim11())->getDefaultLanguage();

        if (in_array($defaultLanguage, $languages)) {
            $queryBuilder->andWhere("$alias.code = :$exact OR $alias.name LIKE :$full OR $alias.code LIKE :$start OR $alias.synonyms LIKE :$full");
        }

        foreach ($languages as $language) {
            if ($language !== $defaultLanguage) {
                $queryBuilder
                    ->leftJoin("$alias.translations", "tr_$language", Join::WITH, "tr_$language.lang = :tr_$language");

                $or = [
                    "$alias.code = :$exact",
                    "$alias.code LIKE :$start",
                    "tr_$language.field = 'name' AND tr_$language.translation LIKE :$full",
                    "tr_$language.field = 'synonyms' AND tr_$language.translation LIKE :$full",
                ];

                $queryBuilder->andWhere(new Orx($or));
                $queryBuilder->setParameter("tr_$language", $language);
            }
        }

        $value = $this->cleanValue($value);

        $queryBuilder->setParameter($full, "%$value%");
        $queryBuilder->setParameter($start, "$value%");
        $queryBuilder->setParameter($exact, "$value");

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
