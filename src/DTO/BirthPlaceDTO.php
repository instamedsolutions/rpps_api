<?php

namespace App\DTO;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\StateProvider\BirthPlacesProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'BirthPlace',
    operations: [
        new GetCollection(
            uriTemplate: '/birth_places',
            normalizationContext: ['groups' => ['read']],
            provider: BirthPlacesProvider::class
        ),
        new Get(
            uriTemplate: '/birth_places/{code}',
            normalizationContext: ['groups' => ['read']],
            provider: BirthPlacesProvider::class
        ),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
class BirthPlaceDTO
{
    public function __construct(
        #[Groups(['read'])]
        public string $label,
        #[Groups(['read'])]
        public string $code,
        #[Groups(['read'])]
        public string $type,
    ) {
    }
}
