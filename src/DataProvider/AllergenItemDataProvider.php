<?php
// api/src/DataProvider/DrugItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Allergen;
use App\Entity\Drug;
use App\Entity\RPPS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AllergenItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{

    public function __construct(protected EntityManagerInterface $em)
    {
    }


    /**
     * @param string|null $operationName
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Allergen::class === $resourceClass && "get" === $operationName;
    }


    /**
     * @param array|int|string $id
     * @param string|null $operationName
     * @return Drug|null
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Allergen
    {
        return $this->em->getRepository(Allergen::class)->find($id);
    }
}
