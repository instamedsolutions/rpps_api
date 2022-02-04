<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\DiseaseFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\DiseaseRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 *
 * @ORM\Entity(repositoryClass=DiseaseRepository::class)
 *
 * @ORM\Table(name="diseases",indexes={
 *     @ORM\Index(name="diseases_index", columns={"cim"})
 * })
 *
 * @ApiFilter(RangeFilter::class,properties={"hierarchyLevel"})
 *
 * @UniqueEntity("cim")
 *
 * @ApiFilter(DiseaseFilter::class,properties={"search"})
 * @ApiFilter(SearchFilter::class, properties={"category.cim","group.cim"})
 *
 */
class Disease extends Thing implements Entity
{

    const SEX_MALE = 1;

    const SEX_FEMALE = 2;

    /**
     *
     * @var string|null
     *
     * The unique CIS Id in the government database
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="66595239"
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
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="PANTOPRAZOLE KRKA 40 mg, comprimé gastro-résistant"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var Disease|null
     *
     * @Groups({"diseases:item:read"})
     *
     * @MaxDepth(2)
     *
     * The parent disease (if any)
     *
     * @ORM\ManyToOne(targetEntity="Disease", inversedBy="children", cascade={"persist"})
     */
    protected $parent;


    /**
     * @var DiseaseGroup|null
     *
     * @Groups({"diseases:read"})
     *
     * @MaxDepth(1)
     *
     * The subgroup the disease is a part of.
     * A group is itself linked to a category
     *
     * @ORM\ManyToOne(targetEntity="DiseaseGroup", cascade={"persist"})
     */
    protected $group;

    /**
     * @var DiseaseGroup|null
     *
     * The main category the disease is a part of.
     *
     * @MaxDepth(1)
     *
     * @Groups({"diseases:read"})
     *
     * @ORM\ManyToOne(targetEntity="DiseaseGroup", cascade={"persist"})
     */
    protected $category;



    /**
     *
     * All the child diseases
     *
     * @ApiSubresource(maxDepth=2)
     *
     * @var Collection|Disease[]
     *
     * @ORM\OneToMany(targetEntity="Disease", mappedBy="parent", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $children = [];


    /**
     *
     * @ORM\Column(type="smallint")
     *
     * @Groups({"diseases:read"})
     *
     * @ApiFilter(RangeFilter::class)
     *
     * @var int
     */
    protected $hierarchyLevel;

    /**
     * @var int|null
     *
     * @Groups({"diseases:item:read"})
     *
     * @ORM\Column(type="smallint",nullable=true)
     *
     */
    protected $sex;


    /**
     *
     * @Groups({"diseases:item:read"})
     *
     * @ORM\Column(type="integer",nullable=true)
     *
     * @var int|null
     */
    protected $lowerAgeLimit;

    /**
     *
     *
     * @Groups({"diseases:item:read"})
     *
     * @ORM\Column(type="integer",nullable=true)
     *
     * @var int|null
     */
    protected $upperAgeLimit;


    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();

    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
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
        $this->cim = $cim;
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
     * @return Disease|null
     */
    public function getParent(): ?Disease
    {
        return $this->parent;
    }

    /**
     * @param Disease|null $parent
     */
    public function setParent(?Disease $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return int
     */
    public function getHierarchyLevel(): int
    {
        return $this->hierarchyLevel;
    }

    /**
     * @param int $hierarchyLevel
     */
    public function setHierarchyLevel(int $hierarchyLevel): void
    {
        $this->hierarchyLevel = $hierarchyLevel;
    }

    /**
     * @return int|null
     */
    public function getSex(): ?int
    {
        return $this->sex;
    }

    /**
     * @param int|null|string $sex
     */
    public function setSex($sex): void
    {
        if(is_string($sex)) {
            $sex = $this->parseSex($sex);
        }
        $this->sex = $sex;
    }

    /**
     * @return int|null
     */
    public function getLowerAgeLimit(): ?int
    {
        return $this->lowerAgeLimit;
    }

    /**
     * @param int|string|null $lowerAgeLimit
     */
    public function setLowerAgeLimit($lowerAgeLimit): void
    {
        if(is_string($lowerAgeLimit)) {
            $lowerAgeLimit = $this->parseAgeLimit($lowerAgeLimit);
        }
        $this->lowerAgeLimit = $lowerAgeLimit;
    }

    /**
     * @return int|string|null
     */
    public function getUpperAgeLimit(): ?int
    {
        return $this->upperAgeLimit;
    }

    /**
     * @param int|null|string $upperAgeLimit
     */
    public function setUpperAgeLimit($upperAgeLimit): void
    {
        if(is_string($upperAgeLimit)) {
            $upperAgeLimit = $this->parseAgeLimit($upperAgeLimit);
        }
        $this->upperAgeLimit = $upperAgeLimit;
    }

    /**
     * @return DiseaseGroup|null
     */
    public function getGroup(): ?DiseaseGroup
    {
        return $this->group;
    }

    /**
     * @param DiseaseGroup|null $group
     */
    public function setGroup(?DiseaseGroup $group): void
    {
        $this->group = $group;
    }

    /**
     * @return DiseaseGroup|null
     */
    public function getCategory(): ?DiseaseGroup
    {
        return $this->category;
    }

    /**
     * @param DiseaseGroup|null $category
     */
    public function setCategory(?DiseaseGroup $category): void
    {
        $this->category = $category;
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getName();
    }


    /**
     * @param string $ageLimit
     * @return int
     */
    protected function parseAgeLimit(string $ageLimit) : ?int
    {
        if($ageLimit === "9999") {
            return null;
        }

        if(strpos($ageLimit,"t") === 0) {
            return (int)(trim($ageLimit,"t"));
        }

        $ageLimit = (int)str_replace("j","",$ageLimit);

        return $ageLimit*365;

    }


    /**
     * @param string $sex
     * @return int|null
     */
    protected function parseSex(string $sex) : ?int
    {
        $sexes = [
            "9" => null,
            "M" => self::SEX_MALE,
            "W" => self::SEX_FEMALE
        ];

        return $sexes[$sex];
    }

}
