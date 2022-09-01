<?php
// api/src/DataProvider/DrugItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Allergen;
use App\Entity\CCAM;
use App\Entity\Disease;
use App\Entity\Drug;
use App\Entity\Entity;
use App\Entity\NGAP;
use Doctrine\ORM\EntityManagerInterface;

final class DefaultItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{

    public function __construct(protected EntityManagerInterface $em)
    {
    }


    /**
     * @param string|null $operationName
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return in_array($resourceClass, $this->getSupportedEntities()) && "get" === $operationName;
    }


    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Entity
    {
        return $this->em->getRepository($resourceClass)->find($id);
    }

    private function getSupportedEntities(): array
    {
        return [
            Allergen::class,
            CCAM::class,
            Disease::class,
            Drug::class,
            NGAP::class
        ];
    }


}
