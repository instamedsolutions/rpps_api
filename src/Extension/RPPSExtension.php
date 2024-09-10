<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\RPPS;
use Doctrine\ORM\QueryBuilder;

class RPPSExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (RPPS::class !== $operation->getClass()) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$rootAlias.specialty IS NULL OR $rootAlias.specialty != :specialty_ignored");
        $queryBuilder->setParameter('specialty_ignored', 'Infirmier');
    }
}
