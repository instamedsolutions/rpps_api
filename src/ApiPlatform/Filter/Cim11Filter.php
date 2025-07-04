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
    protected function addSearchFilter(QueryBuilder $qb, ?string $value, array $languages = []): QueryBuilder
    {
        if (!$value) {
            return $qb;
        }

        $alias = $qb->getRootAliases()[0];
        $clean = $this->cleanValue(trim($value));
        $defaultLang = (new Cim11())->getDefaultLanguage();
        $langs = $languages ?: [$defaultLang];

        $pExact = $this->queryNameGenerator->generateParameterName('exact');
        $pStart = $this->queryNameGenerator->generateParameterName('start');

        $qb->setParameter($pExact, $clean)
            ->setParameter($pStart, $clean . '%');

        $tokens = array_filter(array_map([$this, 'cleanValue'], explode(' ', $value)));

        $languageClauses = [];  // will be OR‑ed together

        foreach ($langs as $lang) {
            if ($lang === $defaultLang) {
                $clauses = [
                    "$alias.code = :$pExact",
                    "$alias.code LIKE :$pStart",
                ];

                if ($tokens) {
                    $and = [];
                    foreach ($tokens as $tok) {
                        $p = $this->queryNameGenerator->generateParameterName('tok');
                        $qb->setParameter($p, "%$tok%");
                        $and[] = "($alias.name LIKE :$p OR $alias.synonyms LIKE :$p)";
                    }
                    $clauses[] = '(' . implode(' AND ', $and) . ')';
                } else {
                    $pFull = $this->queryNameGenerator->generateParameterName('full');
                    $qb->setParameter($pFull, "%$clean%");
                    $clauses[] = "$alias.name LIKE :$pFull";
                    $clauses[] = "$alias.synonyms LIKE :$pFull";
                }

                $languageClauses[] = '(' . implode(' OR ', $clauses) . ')';
            } else {
                $qb->leftJoin("$alias.translations", "tr_$lang", Join::WITH, "tr_$lang.lang = :lang_$lang")
                    ->setParameter("lang_$lang", $lang);

                $trs = [];
                foreach ($tokens ?: [$clean] as $frag) {
                    $p = $this->queryNameGenerator->generateParameterName('frag');
                    $qb->setParameter($p, "%$frag%");
                    $trs[] = "tr_$lang.translation LIKE :$p";
                }

                $languageClauses[] =
                    "(tr_$lang.field IN ('name','synonyms') AND (" . implode(' AND ', $trs) . '))';
            }
        }

        // a record can match in *any* language
        $qb->andWhere(new Orx($languageClauses));

        return $qb;
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
