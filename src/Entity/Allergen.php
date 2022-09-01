<?php

namespace App\Entity;

use Stringable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\AllergenFilter;
use App\Repository\AllergenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;


// Liste extracted from
// https://biologiepathologie.chu-lille.fr/fichiers/42_795catalogue-rast-i.pdf

#[ApiFilter(AllergenFilter::class, properties: ["search"])]
#[ORM\Entity(repositoryClass: AllergenRepository::class)]
#[ORM\Table(name: 'allergens')]
#[ORM\Index(name: 'allergens_index', columns: ['allergen_code'])]
#[UniqueEntity('code')]
class Allergen extends Thing implements Entity, Stringable
{

    #[ApiProperty(description: "The unique code of the allergen", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "01"
        ]
    ])]
    #[ApiFilter(SearchFilter::class, strategy: "exact")]
    #[Groups(['read'])]
    #[ORM\Column(name: 'allergen_code', type: 'string', length: 10, unique: true)]
    protected ?string $code;


    #[ApiProperty(description: "The name of the allergen", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "Corn"
        ]
    ])]
    #[ApiFilter(SearchFilter::class, strategy: "istart")]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name;


    #[ApiProperty(description: "The parent group of the allergen", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "Pollens de graminées"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(name: 'allergen_group', type: 'string', length: 255)]
    protected ?string $group;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = trim($code);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = trim($name);
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): void
    {
        $this->group = trim($group);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getName();
    }
}
