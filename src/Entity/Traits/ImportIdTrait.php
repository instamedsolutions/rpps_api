<?php

namespace App\Entity\Traits;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;

trait ImportIdTrait
{
    #[ApiProperty(readable: false, writable: false)]
    #[ORM\Column(name: 'import_id', type: 'string', length: 20, nullable: false)]
    private ?string $importId = null;

    public function getImportId(): ?string
    {
        return $this->importId;
    }

    public function setImportId(?string $importId): void
    {
        $this->importId = $importId;
    }
}
