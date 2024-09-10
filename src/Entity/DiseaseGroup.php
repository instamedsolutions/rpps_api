<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\ApiPlatform\Filter\DiseaseGroupFilter;
use App\Repository\DiseaseGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiFilter(DiseaseGroupFilter::class, properties: ['search'])]
#[ORM\Entity(repositoryClass: DiseaseGroupRepository::class)]
#[ORM\Table(name: 'diseases_group')]
#[UniqueEntity('cim')]
#[ApiResource(
    shortName: 'cim10Group',
    operations: [
        new GetCollection(
            order: ['name' => 'ASC'],
        ),
        new GetCollection(
            uriTemplate: '/cim10_groups/{id}/children{._format}',
            uriVariables: [
                'id' => new Link(toProperty: 'parent', fromClass: DiseaseGroup::class),
            ]
        ),
        new Get(),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
class DiseaseGroup extends Thing implements Entity, Stringable
{
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ApiProperty(
        description: 'The unique CIS Id in the government database',
        required: true,
        openapiContext: [
            'type' => 'string',
            'example' => '1',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $cim = null;

    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ApiProperty(
        description: 'The name of the disease group',
        required: true,
        openapiContext: [
            'type' => 'string',
            'example' => 'Certaines maladies infectieuses et parasitaires',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name = null;

    #[Groups(['diseases_group:read'])]
    #[ORM\ManyToOne(targetEntity: DiseaseGroup::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?DiseaseGroup $parent = null;

    /**
     * @var Collection<int,DiseaseGroup>
     */
    #[Groups(['diseases_groups:item:read'])]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: DiseaseGroup::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    protected Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();

        parent::__construct();
    }

    public function getCim(): ?string
    {
        return $this->cim;
    }

    public function setCim(?string $cim): void
    {
        $this->cim = trim($cim);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getParent(): ?DiseaseGroup
    {
        return $this->parent;
    }

    public function setParent(?DiseaseGroup $parent): void
    {
        if ($this->parent instanceof DiseaseGroup) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if ($parent instanceof DiseaseGroup) {
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

    public function addChild(DiseaseGroup $diseaseGroup): void
    {
        if (!$this->getChildren()->contains($diseaseGroup)) {
            $this->children->add($diseaseGroup);
        }
    }

    public function removeChild(DiseaseGroup $diseaseGroup): void
    {
        $this->children->removeElement($diseaseGroup);
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
