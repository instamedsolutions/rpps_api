<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Drug;
use App\Entity\RPPS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class DrugItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{


    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @param string|null $operationName
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Drug::class === $resourceClass && "get" === $operationName;
    }


    /**
     * @param array|int|string $id
     * @param string|null $operationName
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Drug
    {
        return $this->em->getRepository(Drug::class)->find($id);
    }
}
