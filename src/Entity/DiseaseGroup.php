<?php

namespace App\Entity;

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
/**
 *
 * @ORM\Entity(repositoryClass=DiseaseGroupRepository::class)
 *
 * @ORM\Table(name="diseases_group")
 *
 * @UniqueEntity("cim")
 *
 * @ApiFilter(DiseaseGroupFilter::class,properties={"search"})
 *
 */
class DiseaseGroup extends Thing implements Entity
{

    /**
     *
     * @var string|null
     *
     * The unique CIS Id in the government database
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
     *              "example"="1"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string",unique=true)
     */
    protected $cim;

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
     * @ORM\Column(type="string", length=255)
     */
    protected $name;


    /**
     * @var DiseaseGroup|null
     *
     * @Groups({"diseases_groups:read"})
     *
     * All the events that are linked to the user
     *
     * @ORM\ManyToOne(targetEntity="DiseaseGroup", inversedBy="children", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $parent;


    /**
     *
     * @Groups({"diseases_groups:item:read"})
     *
     * @ApiSubresource(maxDepth=2)
     *
     * @var Collection|DiseaseGroup[]
     *
     * @ORM\OneToMany(targetEntity="DiseaseGroup", mappedBy="parent", cascade={"persist"}, fetch="EXTRA_LAZY")
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
    public function getCim(): ?string
    {
        return $this->cim;
    }

    /**
     * @param string|null $cim
     */
    public function setCim(?string $cim): void
    {
        $this->cim = trim($cim);
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
        $this->name = $name;
    }

    /**
     * @return DiseaseGroup|null
     */
    public function getParent(): ?DiseaseGroup
    {
        return $this->parent;
    }

    /**
     * @param DiseaseGroup|null $parent
     */
    public function setParent(?DiseaseGroup $parent): void
    {
        if($this->parent instanceof DiseaseGroup) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if($parent instanceof DiseaseGroup) {
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
     * @param DiseaseGroup $diseaseGroup
     */
    public function addChild(DiseaseGroup $diseaseGroup)
    {
        if(!$this->getChildren()->contains($diseaseGroup)) {
            $this->children->add($diseaseGroup);
        }
    }

    /**
     * @param DiseaseGroup $diseaseGroup
     */
    public function removeChild(DiseaseGroup $diseaseGroup)
    {
        $this->children->removeElement($diseaseGroup);
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getName();
    }





}
