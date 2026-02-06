<?php

namespace App\StateProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

final class DefaultItemDataProvider implements ProviderInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            /* @phpstan-ignore-next-line */
            return $this->em->getRepository($operation->getClass())->find($uriVariables['id']);
        }
        throw new Exception('This operation is not supported');
    }
}
