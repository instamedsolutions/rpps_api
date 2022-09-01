<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Disease;
use Doctrine\ORM\EntityManagerInterface;

final class DiseaseDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{

    public function __construct(protected EntityManagerInterface $em)
    {
    }


    /**
     * @param string|null $operationName
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Disease::class === $resourceClass && "get" === $operationName;
    }


    /**
     * @param array|int|string $id
     * @param string|null $operationName
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Disease
    {
        return $this->em->getRepository(Disease::class)->find($id);
    }
}
