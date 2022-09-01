<?php
// api/src/DataProvider/RPPSItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\RPPS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class RPPSItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{

    public function __construct(protected readonly RequestStack $requestStack, protected EntityManagerInterface $em)
    {
    }

    /**
     * @param string|null $operationName
     */
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return RPPS::class === $resourceClass && "get" === $operationName;
    }


    /**
     * @param array|int|string $id
     * @param string|null $operationName
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?RPPS
    {
        $request = $this->requestStack->getMainRequest();

        if (0 === $id) {
            $id = $request->get("id", null);
        }

        if (null === $id) {
            return null;
        }

        // Some uuId start with 00
        if (!str_contains((string)$id, '-')) {
            $id = preg_replace('#^0+#', '', $id);
        }

        return $this->em->getRepository(RPPS::class)->find($id);
    }
}
