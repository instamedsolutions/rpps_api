<?php

namespace App\Controller;

use App\Service\BirthPlaceService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BirthPlaceController extends AbstractController
{
    public function __construct(private readonly BirthPlaceService $birthPlaceService)
    {
    }

    #[Route('/api/birth_places', methods: ['GET'])]
    public function getBirthPlaces(Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        $dateOfBirth = $request->query->get('dateOfBirth');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(50, (int) $request->query->get('limit', 10)));

        if (!$search) {
            throw new BadRequestHttpException('Missing parameter : search');
        }

        $parsedDate = null;
        if ($dateOfBirth) {
            try {
                $parsedDate = new DateTime($dateOfBirth);
            } catch (Exception) {
            }
        }

        if ($parsedDate) {
            $results = $this->birthPlaceService->searchBirthPlacesByDate($search, $parsedDate);
        } else {
            $results = $this->birthPlaceService->searchBirthPlaces($search);
        }

        // Sort results by label
        usort($results, static fn ($a, $b) => strcmp($a->label, $b->label));

        // Pagination logic
        $totalItems = count($results);
        $offset = ($page - 1) * $limit;
        $paginatedResults = array_slice($results, $offset, $limit);
        $totalPages = (int) ceil($totalItems / $limit);

        return new JsonResponse([
            'page' => $page,
            'limit' => $limit,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'data' => $paginatedResults,
        ]);
    }
}
