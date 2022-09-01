<?php

namespace App\Entity;

use Stringable;
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


#[ApiFilter(RangeFilter::class, properties: ["hierarchyLevel"])]
#[ApiFilter(DiseaseFilter::class, properties: ["search"])]
#[ApiFilter(SearchFilter::class, properties: ["category.cim", "group.cim"])]
#[ORM\Entity(repositoryClass: DiseaseRepository::class)]
#[ORM\Table(name: 'diseases')]
#[ORM\Index(columns: ['cim'], name: 'diseases_index')]
#[UniqueEntity('cim')]
class Disease extends Thing implements Entity, Stringable
{
    final const SEX_MALE = 1;

    final const SEX_FEMALE = 2;


    #[ApiFilter(SearchFilter::class, strategy: "exact")]
    #[ApiProperty(description: "The unique CIM-10 Id in the international database", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "66595239"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $cim;

    #[ApiProperty(description: "The name of the disease", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "PANTOPRAZOLE KRKA 40 mg, comprimé gastro-résistant"
        ]
    ])]
    #[ApiFilter(SearchFilter::class, strategy: "istart")]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name;


    #[ApiProperty(description: "The parent disease (if any)", required: false)]
    #[Groups(['diseases:item:read'])]
    #[MaxDepth(2)]
    #[ORM\ManyToOne(targetEntity: Disease::class, cascade: ['persist'], inversedBy: 'children')]
    protected ?Disease $parent;


    #[ApiProperty(description: "The subgroup the disease is a part of. A group is itself linked to a category", required: false)]
    #[Groups(['diseases:read'])]
    #[MaxDepth(1)]
    #[ORM\ManyToOne(targetEntity: DiseaseGroup::class, cascade: ['persist'])]
    protected ?DiseaseGroup $group;


    #[ApiProperty(description: "The main category the disease is a part of", required: false)]
    #[MaxDepth(1)]
    #[Groups(['diseases:read'])]
    #[ORM\ManyToOne(targetEntity: DiseaseGroup::class, cascade: ['persist'])]
    protected ?DiseaseGroup $category;

    /**
     *
     * @var Collection<int,Disease>
     */
    #[ApiProperty(description: "The child diseases", required: false)]
    #[ApiSubresource(maxDepth: 2)]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Disease::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    protected Collection $children;


    #[ApiProperty(description: "The hierarchy level of the disease in the tree", required: false)]
    #[ApiFilter(RangeFilter::class)]
    #[ORM\Column(type: 'smallint')]
    #[Groups(['diseases:read'])]
    protected ?int $hierarchyLevel;


    #[ApiProperty(description: "The sex of the patient if the disease only targets a specific individual", required: false)]
    #[Groups(['diseases:item:read'])]
    #[ORM\Column(type: 'smallint', nullable: true)]
    protected ?int $sex;


    #[Groups(['diseases:item:read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $lowerAgeLimit;


    #[Groups(['diseases:item:read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $upperAgeLimit;


    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCim(): ?string
    {
        return $this->cim;
    }

    public function setCim(?string $cim): void
    {
        $this->cim = $cim;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getParent(): ?Disease
    {
        return $this->parent;
    }

    public function setParent(?Disease $parent): void
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

    public function getSex(): ?int
    {
        return $this->sex;
    }

    /**
     * @param int|string|null $sex
     */
    public function setSex(int|string|null $sex): void
    {
        if (is_string($sex)) {
            $sex = $this->parseSex($sex);
        }
        $this->sex = $sex;
    }

    public function getLowerAgeLimit(): ?int
    {
        return $this->lowerAgeLimit;
    }

    /**
     * @param int|string|null $lowerAgeLimit
     */
    public function setLowerAgeLimit(int|string|null $lowerAgeLimit): void
    {
        if (is_string($lowerAgeLimit)) {
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
     * @param int|string|null $upperAgeLimit
     */
    public function setUpperAgeLimit(int|string|null $upperAgeLimit): void
    {
        if (is_string($upperAgeLimit)) {
            $upperAgeLimit = $this->parseAgeLimit($upperAgeLimit);
        }
        $this->upperAgeLimit = $upperAgeLimit;
    }

    public function getGroup(): ?DiseaseGroup
    {
        return $this->group;
    }

    public function setGroup(?DiseaseGroup $group): void
    {
        $this->group = $group;
    }

    public function getCategory(): ?DiseaseGroup
    {
        return $this->category;
    }

    public function setCategory(?DiseaseGroup $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }


    protected function parseAgeLimit(string $ageLimit): ?int
    {
        if ($ageLimit === "9999") {
            return null;
        }

        if (str_starts_with($ageLimit, "t")) {
            return (int)(trim($ageLimit, "t"));
        }

        $ageLimit = (int)str_replace("j", "", $ageLimit);

        return $ageLimit * 365;
    }

    protected function parseSex(string $sex): ?int
    {
        $sexes = [
            "9" => null,
            "M" => self::SEX_MALE,
            "W" => self::SEX_FEMALE
        ];

        return $sexes[$sex];
    }
}
