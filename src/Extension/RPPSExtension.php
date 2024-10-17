<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\RPPS;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class RPPSExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (RPPS::class !== $operation->getClass()) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request->query->getBoolean('include_paramedical', false)) {
            return;
        }

        $specialtyIds = $this->em->getConnection()->fetchFirstColumn('SELECT id from specialty where is_paramedical = 0');

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere("$rootAlias.specialtyEntity IN (:specialtyIds)");
        $queryBuilder->setParameter('specialtyIds', $specialtyIds);
    }
}
