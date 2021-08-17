<?php

namespace App\Entity;

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

/**
 *
 * @ORM\Entity(repositoryClass=CCAMGroupRepository::class)
 *
 * @ORM\Table(name="ccam_group")
 *
 * @UniqueEntity("code")
 *
 *
 */
class CCAMGroup extends Thing implements Entity
{

    /**
     *
     * @var string|null
     *
     * The unique code in the government database
     *
     * @Groups({"read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="01"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string",unique=true)
     */
    protected $code;

    /**
     *
     * @var string|null
     *
     * The name of the drug
     *
     * @Groups({"read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Certaines maladies infectieuses et parasitaires"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, options={"collation":"utf8mb4_unicode_ci"})
     */
    protected $name;

    /**
     *
     * @var string|null
     *
     * The name of the drug
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @Groups({"ccam_groups:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Électromyographie par électrode de surface, avec enregistrement vidéo"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="text", options={"collation":"utf8mb4_unicode_ci"},nullable=true)
     */
    protected $description;


    /**
     * @var CCAMGroup|null
     *
     * @Groups({"ccam_groups:read"})
     *
     * All the events that are linked to the user
     *
     * @ORM\ManyToOne(targetEntity="CCAMGroup", inversedBy="children", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $parent;


    /**
     *
     * @Groups({"ccam_groups:item:read"})
     *
     * @ApiSubresource(maxDepth=2)
     *
     * @var Collection|CCAMGroup[]
     *
     * @ORM\OneToMany(targetEntity="CCAMGroup", mappedBy="parent", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $children = [];



    public function __construct()
    {
        $this->children = new ArrayCollection();

        parent::__construct();

    }


    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }


    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }


    /**
     * @param string $description
     */
    public function addDescriptionLine(string $description)
    {
        if(trim($description)) {
            $this->description .= trim(",$description");
        }
    }


    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = ucfirst(mb_strtolower($name));
    }

    /**
     * @return CCAMGroup|null
     */
    public function getParent(): ?CCAMGroup
    {
        return $this->parent;
    }

    /**
     * @param CCAMGroup|null $parent
     */
    public function setParent(?CCAMGroup $parent): void
    {
        if($this->parent instanceof CCAMGroup) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if($parent instanceof CCAMGroup) {
            $parent->addChild($this);
        }

    }

    /**
     * @return array|ArrayCollection
     */
    public function getChildren()
    {
        if(!$this->children) {
            $this->children = new ArrayCollection();
        }

        return $this->children;
    }

    /**
     * @param array|ArrayCollection $children
     */
    public function setChildren($children): void
    {
        $this->children = $children;
    }


    /**
     * @param CCAMGroup $cCAMGroup
     */
    public function addChild(CCAMGroup $cCAMGroup)
    {
        if(!$this->getChildren()->contains($cCAMGroup)) {
            $this->children->add($cCAMGroup);
        }
    }

    /**
     * @param CCAMGroup $cCAMGroup
     */
    public function removeChild(CCAMGroup $cCAMGroup)
    {
        $this->children->removeElement($cCAMGroup);
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getName();
    }





}
