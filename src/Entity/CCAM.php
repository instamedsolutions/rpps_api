<?php

namespace App\Entity;

use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use Stringable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Repository\CCAMRepository;
use App\ApiPlatform\Filter\CCAMFilter;


// http://www.cpam21.fr/Flashs/flashs/Medecins/Docs/SC506_fichesCCAM.pdf
#[ApiFilter(CCAMFilter::class, properties: ["search"])]
#[ApiFilter(SearchFilter::class, properties: ["category.code", "group.code"])]
#[ORM\Entity(repositoryClass: CCAMRepository::class)]
#[ORM\Table(name: 'ccam')]
#[ORM\Index(name: 'ccam_index', columns: ['code'])]
#[UniqueEntity('code')]
class CCAM extends Thing implements Entity, Stringable
{

    #[ApiProperty(
        description: "The uniq code of the CCAM",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "string",
                "example" => "66595239"
            ]
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    protected ?string $code = null;


    #[ApiProperty(
        description: "The name of the CCAM",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "string",
                "example" => "Électromyographie par électrode de surface, avec enregistrement vidéo"
            ]
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_START)]
    #[Groups(['read'])]
    #[ORM\Column(type: 'text')]
    protected ?string $name = null;


    #[ApiProperty(
        description: "The description of the CCAM",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "string",
                "example" => "Électromyographie par électrode de surface, avec enregistrement vidéo"
            ]
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_START)]
    #[Groups(['ccams:item:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;


    #[ApiProperty(
        description: "The price rate for the secteur 1",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "number",
                "example" => 33.56
            ]
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $rate1 = null;


    #[ApiProperty(
        description: "The price rate for the secteur 2",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "number",
                "example" => 33.56
            ]
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $rate2 = null;

    #[ApiProperty(
        description: "The group the CCAM is a part of",
        required: true,
        attributes: [
            "openapi_context" => [
                '$ref' => "#/components/schemas/CCAMGroup",
                "example" => "66595239"
            ]
        ]
    )]
    #[Groups(['ccams:read'])]
    #[MaxDepth(1)]
    #[ORM\ManyToOne(targetEntity: CCAMGroup::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    protected ?CCAMGroup $group  = null;


    #[ApiProperty(
        description: "The category the CCAM is a part of",
        required: true,
        attributes: [
            "openapi_context" => [
                '$ref' => "#/components/schemas/CCAMGroup",
                "example" => "66595239"
            ]
        ]
    )]
    #[MaxDepth(1)]
    #[Groups(['ccams:item:read'])]
    #[ORM\ManyToOne(targetEntity: CCAMGroup::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    protected ?CCAMGroup $category = null;


    #[Groups(['ccams:read'])]
    #[ORM\Column(type: 'json')]
    protected array $modifiers = [];


    #[ApiProperty(
        description: "The unique regroupement code in the government database",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "string",
                "example" => "ADE"
            ]
        ]
    )]
    #[Groups(['read'])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[ORM\Column(type: 'string', length: 4, nullable: false)]
    protected ?string $regroupementCode = null;


    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
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
            $this->description .= trim((string)",$description");
        }
    }

    public function getRate1(): ?float
    {
        return $this->rate1;
    }

    public function setRate1(?float $rate1): void
    {
        $this->rate1 = $rate1;
    }

    public function getRate2(): ?float
    {
        return $this->rate2;
    }

    public function setRate2(?float $rate2): void
    {
        $this->rate2 = $rate2;
    }

    public function getGroup(): ?CCAMGroup
    {
        return $this->group;
    }

    public function setGroup(?CCAMGroup $group): void
    {
        $this->group = $group;
    }

    public function getCategory(): ?CCAMGroup
    {
        return $this->category;
    }

    public function setCategory(?CCAMGroup $category): void
    {
        $this->category = $category;
    }

    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    public function setModifiers(array $modifiers): void
    {
        $this->modifiers = $modifiers;
    }

    public function getRegroupementCode(): ?string
    {
        return $this->regroupementCode;
    }

    public function setRegroupementCode(?string $regroupementCode): void
    {
        $this->regroupementCode = $regroupementCode;
    }


    public function __toString(): string
    {
        return (string)$this->getName();
    }
}
