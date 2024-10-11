<?php

namespace App\StateProvider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CityRepository;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SimilarCitiesProvider implements ProviderInterface
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
                throw new NotFoundHttpException('City not found');
            }

            // TODO Fix ?
            //$limit = $context['filters']['limit'] ?? 10;
            $limit = 20;

            if (!$city->getLatitude() || !$city->getLongitude()) {
                // Try to find a sub city with coordinates
                $subCityWithCoordinates = $this->cityRepository->findSubCityWithCoordinates($city);

                if (!$subCityWithCoordinates) {
                    // If not found, take any city in the same department
                    return $this->cityRepository->findSimilarCitiesInDepartment($city, $limit);
                } else {
                    $city->setLongitude($subCityWithCoordinates->getLongitude()) ;
                    $city->setLatitude($subCityWithCoordinates->getLatitude());
                }
            }

            return $this->cityRepository->findSimilarCitiesByCoordinates($city, $limit);
        }

        //  throw new Exception('This operation is not supported');
        throw new Exception('This operation is not supported');
    }
}
