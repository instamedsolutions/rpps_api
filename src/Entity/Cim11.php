<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\Cim11Filter;
use App\Repository\Cim11Repository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: Cim11Repository::class)]
#[ApiFilter(Cim11Filter::class, properties: ['search'])]
#[ORM\Table(name: 'cim_11')]
#[ORM\Index(columns: ['code'])]
#[UniqueEntity(['code', 'whoId'])]
class Cim11 extends Thing implements Entity, Stringable
{
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ApiProperty(description: 'The unique CIM-10 Id in the international database', required: true, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '1A00',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 16, unique: true)]
    protected ?string $code = null;

    #[ApiProperty(description: 'The name of the disease', required: true, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'Choléra',
        ],
    ])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_START)]
    #[Groups(['read'])]
    #[ORM\Column(type: 'text')]
    protected ?string $name = null;

    #[ApiProperty(description: 'The parent disease (if any)', required: false)]
    #[ORM\ManyToOne(targetEntity: Cim11::class, cascade: ['persist'], inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Cim11 $parent = null;

    /**
     * @var Collection<int,Disease>
     */
    #[ApiProperty(description: 'The child diseases', required: false)]
    #[ApiSubresource(maxDepth: 1)]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Cim11::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected Collection $children;

    #[ApiProperty(description: 'The hierarchy level of the disease in the tree', required: false)]
    #[ApiFilter(RangeFilter::class)]
    #[ORM\Column(type: 'smallint')]
    #[Groups(['read'])]
    protected ?int $hierarchyLevel = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    protected ?string $whoId = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'simple_array')]
    protected array $synonyms = [];

    /**
     * @var Collection<int,Cim11Modifier>
     */
    #[Groups(['read'])]
    #[ORM\OneToMany(mappedBy: 'cim11', targetEntity: Cim11Modifier::class, cascade: ['persist', 'remove'])]
    protected Collection $modifiers;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    protected ?string $cim10Code = null;

    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
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

    public function getParent(): ?Cim11
    {
        return $this->parent;
    }

    public function setParent(?Cim11 $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function getHierarchyLevel(): int
    {
        return $this->hierarchyLevel;
    }

    public function setHierarchyLevel(int $hierarchyLevel): void
    {
        $this->hierarchyLevel = $hierarchyLevel;
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

    public function getModifiers(): Collection
    {
        return $this->modifiers;
    }

    public function setModifiers(Collection $modifiers): void
    {
        $this->modifiers = $modifiers;
    }

    public function hasModifier(ModifierType $type): bool
    {
        foreach ($this->modifiers as $modifier) {
            if ($modifier->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    public function addModifier(Cim11Modifier $modifier): void
    {
        if (!$this->modifiers->contains($modifier)) {
            $this->modifiers->add($modifier);
        }
    }

    public function getCim10Code(): ?string
    {
        return $this->cim10Code;
    }

    public function setCim10Code(?string $cim10Code): void
    {
        $this->cim10Code = $cim10Code;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
