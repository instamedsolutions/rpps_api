<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity()]
#[ORM\Table(name: 'cim_11_modifier')]
class Cim11Modifier extends Thing implements Entity, Stringable
{
    // API Only, this field will be populated during the normalisation with the corresponding translation
    #[Groups(['read'])]
    protected ?string $name = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 64, nullable: false, enumType: ModifierType::class)]
    protected ?ModifierType $type = null;

    #[Groups(['cim_11_modifers:read'])]
    #[MaxDepth(1)]
    #[ORM\ManyToOne(targetEntity: Cim11::class, inversedBy: 'modifiers')]
    #[ORM\JoinColumn('cim11_id', onDelete: 'CASCADE')]
    protected Cim11 $cim11;

    #[Groups(['read'])]
    protected bool $multiple = false;

    /**
     * @var Collection<int,Cim11ModifierValue>
     */
    #[Groups(['read'])]
    #[ORM\ManyToMany(targetEntity: Cim11ModifierValue::class, mappedBy: 'modifiers')]
    protected ?Collection $values;

    public function __construct()
    {
        $this->values = new ArrayCollection();
        parent::__construct();
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

    public function getType(): ?ModifierType
    {
        return $this->type;
    }

    public function setType(?ModifierType $type): void
    {
        $this->type = $type;
    }

    public function getCim11(): Cim11
    {
        return $this->cim11;
    }

    public function setCim11(Cim11 $cim11): void
    {
        $this->cim11 = $cim11;
        $cim11->addModifier($this);
    }

    public function getValues(): Collection
    {
        return $this->values ?? new ArrayCollection();
    }

    public function setValues(Collection $values): void
    {
        $this->values = $values;
    }

    public function addValue(Cim11ModifierValue $value): void
    {
        if (!$this->values->contains($value)) {
            $this->values->add($value);
            $value->addModifier($this);
        }
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
