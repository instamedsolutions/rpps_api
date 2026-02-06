<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Entity\Traits\ImportIdTrait;
use App\Repository\CCAMGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CCAMGroupRepository::class)]
#[ORM\Table(name: 'ccam_group')]
#[UniqueEntity('code')] #[ApiResource(
    shortName: 'CcamGroup',
    operations: [
        new GetCollection(
            order: ['name' => 'ASC'],
        ),
        new GetCollection(
            uriTemplate: '/ccam_group/{id}/children{._format}',
            uriVariables: [
                'id' => new Link(toProperty: 'parent', fromClass: CCAMGroup::class),
            ]
        ),
        new Get(),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
class CCAMGroup extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    #[ApiProperty(
        description: 'The unique code in the government database',
        required: true,
        schema: ['type' => 'string', 'example' => '01'],
    )]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $code = null;

    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ApiProperty(
        description: 'The name of the disease group',
        required: true,
        schema: ['type' => 'string', 'example' => 'Certaines maladies infectieuses et parasitaires'],
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name = null;

    #[ApiProperty(
        description: 'The description of the disease group',
        required: true,
        schema: ['type' => 'string', 'example' => 'Certaines maladies infectieuses et parasitaires'],
    )]
    #[Groups(['ccam_groups:item:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[Groups(['ccam_group:read'])]
    #[ORM\ManyToOne(targetEntity: CCAMGroup::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?CCAMGroup $parent = null;

    /**
     * @var Collection<int,CCAMGroup>
     */
    #[Groups(['ccam_groups:item:read'])]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: CCAMGroup::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();

        parent::__construct();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function addDescriptionLine(string $description): void
    {
        if ('' !== trim($description) && '0' !== trim($description)) {
            $this->description .= trim((string) ",$description");
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = ucfirst(mb_strtolower($name));
    }

    public function getParent(): ?CCAMGroup
    {
        return $this->parent;
    }

    public function setParent(?CCAMGroup $parent): void
    {
        if ($this->parent instanceof CCAMGroup) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if ($parent instanceof CCAMGroup) {
            $parent->addChild($this);
        }
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function addChild(CCAMGroup $cCAMGroup): void
    {
        if (!$this->getChildren()->contains($cCAMGroup)) {
            $this->children->add($cCAMGroup);
        }
    }

    public function removeChild(CCAMGroup $cCAMGroup): void
    {
        $this->children->removeElement($cCAMGroup);
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
