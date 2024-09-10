<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity()]
#[ORM\Table(name: 'cim_11_modifier_value')]
#[ORM\Index(columns: ['code'])]
#[UniqueEntity(['code', 'whoId'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
class Cim11ModifierValue extends Thing implements Entity, Stringable
{
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 16, unique: true)]
    protected ?string $code = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'text')]
    protected ?string $name = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    protected ?string $whoId = null;

    #[ORM\Column(type: 'simple_array')]
    protected array $synonyms = [];

    #[ORM\ManyToMany(targetEntity: Cim11Modifier::class, inversedBy: 'values', cascade: ['persist', 'remove'])]
    protected Collection $modifiers;

    public function __construct()
    {
        parent::__construct();
        $this->modifiers = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getWhoId(): ?string
    {
        return $this->whoId;
    }

    public function setWhoId(?string $whoId): void
    {
        $this->whoId = $whoId;
    }

    public function getSynonyms(): array
    {
        return $this->synonyms;
    }

    public function setSynonyms(array $synonyms): void
    {
        $this->synonyms = $synonyms;
    }

    public function addModifier(Cim11Modifier $modifier): void
    {
        if (!$this->modifiers->contains($modifier)) {
            $this->modifiers->add($modifier);
            $modifier->addValue($this);
        }
    }

    public function removeModifier(Cim11Modifier $modifier): void
    {
        $this->modifiers->removeElement($modifier);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
