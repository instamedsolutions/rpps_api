<?php

// api/src/DataProvider/RPPSItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Cim11;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class Cim11ItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(protected readonly RequestStack $requestStack, protected EntityManagerInterface $em)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Cim11::class === $resourceClass && 'get' === $operationName;
    }

    /**
     * @param array|int|string $id
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Cim11
    {
        $request = $this->requestStack->getMainRequest();

        if (0 === $id) {
            $id = $request->get('id', null);
        }

        if (null === $id) {
            return null;
        }

        return $this->em->getRepository(Cim11::class)->find($id);
    }
}
