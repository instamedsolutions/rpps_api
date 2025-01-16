<?php

namespace App\Entity;

interface ImportableEntityInterface
{
    public function getImportId(): ?string;

    public function setImportId(?string $importId): void;
}
