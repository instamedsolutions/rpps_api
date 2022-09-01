<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Stringable;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class Thing implements Entity, ImportedEntity, Stringable
{


    #[Groups(['read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'UUID')]
    #[ApiProperty(description: "The id of the resource", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "format" => "uuid",
        ]
    ])]
    #[ORM\Column(type: 'guid', unique: true)]
    protected ?string $id = null;


    #[ApiProperty(description: "The created date of the entity", writable: false)]
    #[ORM\Column(name: 'created_date', type: 'datetime')]
    protected ?DateTime $createdDate;


    #[ApiProperty(readable: false, writable: false)]
    #[ORM\Column(name: 'import_id', type: 'string', length: 20, nullable: false)]
    public ?string $importId = null;


    /**
     * Thing constructor.
     */
    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }


    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }


    public function setCreatedDate(DateTimeInterface|null $createdDate): void
    {
        $this->createdDate = $createdDate;
    }


    abstract public function __toString(): string;

}
