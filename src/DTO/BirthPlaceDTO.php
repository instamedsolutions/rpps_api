<?php

namespace App\DTO;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class BirthPlaceDTO
{
    public function __construct(
        public string $label,
        public string $code,
        public string $type,
    ) {
    }
}
