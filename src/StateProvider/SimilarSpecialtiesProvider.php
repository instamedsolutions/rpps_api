<?php

namespace App\StateProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\SpecialtyRepository;
use ApiPlatform\Metadata\Get;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SimilarSpecialtiesProvider implements ProviderInterface
{
    private SpecialtyRepository $repository;

    public function __construct(SpecialtyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            $specialty = $this->repository->find($uriVariables['id']);

            if (!$specialty) {
                throw new NotFoundHttpException('Specialty not found');
            }

            // Return the linked specialties
            return $specialty->getSpecialties();
        }
        throw new Exception('This operation is not supported');
    }
}
