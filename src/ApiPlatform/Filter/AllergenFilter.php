<?php

namespace App\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Allergen;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class AllergenFilter extends AbstractFilter
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

        $this->addSearchFilter($queryBuilder, $value, $context['languages'] ?? []);
    }

    /**
     * @throws Exception
     */
    protected function addSearchFilter(QueryBuilder $queryBuilder, ?string $value, array $languages = []): QueryBuilder
    {
        $alias = $queryBuilder->getRootAliases()[0];

        // Generate a unique parameter name to avoid collisions with other filters
        $end = $this->queryNameGenerator->generateParameterName('search');

        $defaultLanguage = (new Allergen())->getDefaultLanguage();

        if (in_array($defaultLanguage, $languages) || !$languages) {
            $queryBuilder->andWhere("$alias.name LIKE :$end OR $alias.group LIKE :$end");
        }

        foreach ($languages as $language) {
            if ($language !== $defaultLanguage) {
                $queryBuilder
                    ->leftJoin("$alias.translations", "tr_$language", Join::WITH, "tr_$language.lang = :tr_$language");

                $or = [
                    "tr_$language.field = 'name' AND tr_$language.translation LIKE :$end",
                    "tr_$language.field = 'group' AND tr_$language.translation LIKE :$end",
                ];

                $queryBuilder->andWhere(new Orx($or));
                $queryBuilder->setParameter("tr_$language", $language);
            }
        }

        $value = $this->cleanValue($value);

        $queryBuilder->setParameter($end, "%$value%");

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
