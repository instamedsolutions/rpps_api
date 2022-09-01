<?php
// api/src/DataProvider/DrugItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\CCAM;
use Doctrine\ORM\EntityManagerInterface;


final class CCAMDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{

    public function __construct(protected EntityManagerInterface $em)
    {
    }


    /**
     * @param string|null $operationName
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CCAM::class === $resourceClass && "get" === $operationName;
    }


    /**
     * @param array|int|string $id
     * @param string|null $operationName
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?CCAM
    {
        return $this->em->getRepository(CCAM::class)->find($id);
    }
}
