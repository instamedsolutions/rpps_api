<?php

namespace App\StateProvider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiPlatform\DtoPaginator;
use App\DTO\BirthPlaceDTO;
use App\Service\BirthPlaceService;
use DateTime;
use Exception;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class BirthPlacesProvider implements ProviderInterface
{
    public function __construct(
        private BirthPlaceService $birthPlaceService,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof GetCollection) {
            return $this->provideItem($operation, $uriVariables, $context);
        }

        if (BirthPlaceDTO::class !== $operation->getClass()) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new LogicException('No current request available');
        }

        $search = $request->query->get('search');
        $dateOfBirth = $request->query->get('dateOfBirth');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 30)));

        if (!$search) {
            return new DtoPaginator([], $page, $limit);
        }

        $parsedDate = null;
        if ($dateOfBirth) {
            try {
                $parsedDate = new DateTime($dateOfBirth);
            } catch (Exception) {
                // silently ignore invalid date
            }
        }

        $results = $parsedDate
            ? $this->birthPlaceService->searchBirthPlacesByDate($search, $parsedDate)
            : $this->birthPlaceService->searchBirthPlaces($search);

        usort(
            $results,
            static fn ($a, $b) => strcmp($a->label, $b->label)
        );

        $unknown = new BirthPlaceDTO('INCONNU', '99999', 'unknown');

        $results[] = $unknown;

        return new DtoPaginator($results, $page, $limit);
    }

    private function provideItem(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if (BirthPlaceDTO::class !== $operation->getClass()) {
            return null;
        }

        if (!isset($uriVariables['code'])) {
            throw new RuntimeException('Missing "code" in URI variables');
        }

        return $this->birthPlaceService->getBirthPlaceByCode($uriVariables['code'], $context['filters']['filters'] ?? null);
    }
}
