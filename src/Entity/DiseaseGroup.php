<?php

namespace App\Entity;

use Stringable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\DiseaseGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use App\ApiPlatform\Filter\DiseaseGroupFilter;

#[ApiFilter(DiseaseGroupFilter::class, properties: ["search"])]
#[ORM\Entity(repositoryClass: DiseaseGroupRepository::class)]
#[ORM\Table(name: 'diseases_group')]
#[UniqueEntity('cim')]
class DiseaseGroup extends Thing implements Entity, Stringable
{

    #[ApiFilter(SearchFilter::class, strategy: "exact")]
    #[ApiProperty(description: "The unique CIS Id in the government database", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "1"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $cim = null;


    #[ApiFilter(SearchFilter::class, strategy: "istart")]
    #[ApiProperty(description: "The name of the disease group", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "Certaines maladies infectieuses et parasitaires"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name = null;


    #[Groups(['diseases_groups:read'])]
    #[ORM\ManyToOne(targetEntity: DiseaseGroup::class, cascade: ['persist','remove'], fetch: 'EXTRA_LAZY', inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true,onDelete: 'SET NULL')]
    protected ?DiseaseGroup $parent = null;


    /**
     * @var Collection<int,DiseaseGroup>
     *
     */
    #[ApiSubresource(maxDepth: 2)]
    #[Groups(['diseases_groups:item:read'])]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: 'DiseaseGroup', cascade: ['persist','remove'], fetch: 'EXTRA_LAZY')]
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

    public function addChild(DiseaseGroup $diseaseGroup)
    {
        if (!$this->getChildren()->contains($diseaseGroup)) {
            $this->children->add($diseaseGroup);
        }
    }

    public function removeChild(DiseaseGroup $diseaseGroup)
    {
        $this->children->removeElement($diseaseGroup);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getName();
    }
}
