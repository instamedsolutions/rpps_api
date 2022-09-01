<?php

namespace App\Entity;

use Stringable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\CCAMGroupRepository;

#[ORM\Entity(repositoryClass: CCAMGroupRepository::class)]
#[ORM\Table(name: 'ccam_group')]
#[UniqueEntity('code')]
class CCAMGroup extends Thing implements Entity, Stringable
{

    #[ApiProperty(description: "The unique code in the government database", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "01"
        ]
    ])]
    #[ApiFilter(SearchFilter::class, strategy: "exact")]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $code;


    #[ApiFilter(SearchFilter::class, strategy: "istart")]
    #[ApiProperty(description: "The name of the disease group", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "Certaines maladies infectieuses et parasitaires"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name;


    #[ApiProperty(description: "The description of the disease group", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "Certaines maladies infectieuses et parasitaires"
        ]
    ])]
    #[Groups(['ccam_groups:item:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description;


    #[Groups(['ccam_groups:read'])]
    #[ORM\ManyToOne(targetEntity: CCAMGroup::class, cascade: ['persist'], fetch: 'EXTRA_LAZY', inversedBy: 'children')]
    protected ?CCAMGroup $parent = null;


    /**
     * @var Collection<int,CCAMGroup>
     */
    #[ApiSubresource(maxDepth: 2)]
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

    public function addDescriptionLine(string $description)
    {
        if (trim($description) !== '' && trim($description) !== '0') {
            $this->description .= trim(",$description");
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

    public function addChild(CCAMGroup $cCAMGroup)
    {
        if (!$this->getChildren()->contains($cCAMGroup)) {
            $this->children->add($cCAMGroup);
        }
    }

    public function removeChild(CCAMGroup $cCAMGroup)
    {
        $this->children->removeElement($cCAMGroup);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getName();
    }
}
