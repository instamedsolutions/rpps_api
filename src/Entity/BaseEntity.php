<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

abstract class BaseEntity implements Entity, Stringable
{
    #[ORM\Id]
    /** @phpstan-ignore-next-line  */
    #[ORM\GeneratedValue(strategy: 'UUID')]
    #[ApiProperty(
        description: 'The id of the resource',
        required: true,
        openapiContext: [
            'type' => 'string',
            'format' => 'uuid',
        ]
    )]
    #[ORM\Column(type: 'guid', unique: true)]
    protected ?string $id = null;

    #[ApiProperty(description: 'The created date of the entity', writable: false)]
    #[ORM\Column(name: 'created_date', type: 'datetime')]
    protected ?DateTimeInterface $createdDate;

    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?DateTimeInterface $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    #[ApiProperty(readable: false, writable: false)]
    public static function parseId(string $id): string
    {
        $id = explode('/', $id);

        return $id[count($id) - 1];
    }

    abstract public function __toString(): string;

    #[SerializedName('id')]
    #[Groups(['read'])]
    public function getEntityId() : string
    {
        $prefix = self::getPrefix();

        $id = str_replace("-","",$this->getId());

        return strtolower("{$prefix}_{$id}");

    }

    public static function getPrefix() : string
    {
        $classParts = explode('\\', static::class);
        return substr(strtolower($classParts[count($classParts) -1]),0,3);
    }

}
