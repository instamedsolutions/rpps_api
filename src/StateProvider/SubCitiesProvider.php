<?php

namespace App\StateProvider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CityRepository;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubCitiesProvider implements ProviderInterface
{
    private CityRepository $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    /**
     * Provides similar cities based on the current city's coordinates.
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof GetCollection) {
            $city = $this->cityRepository->find($uriVariables['id']);

            if (!$city) {
                throw new NotFoundHttpException('City not found for id ' . $uriVariables['id']);
            }

            return $city->getSubCities();
        }
        throw new Exception('This operation is not supported');
    }
}
