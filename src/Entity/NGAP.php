<?php

namespace App\Entity;

use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use App\ApiPlatform\Filter\NGAPFilter;
use App\Repository\NGAPRepository;
use Stringable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;


// https://www.ameli.fr/sites/default/files/Documents/NGAP_%2025072022.pdf
#[ApiFilter(NGAPFilter::class, properties: ["search"])]
#[ORM\Entity(repositoryClass: NGAPRepository::class)]
#[ORM\Table(name: 'ngap')]
#[ORM\Index(name: 'ngap_index', columns: ['code'])]
#[UniqueEntity('code')]
class NGAP extends Thing implements Entity, Stringable
{

    #[ApiProperty(
        description: "The uniq code of the NGAP",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "string",
                "example" => "AAD"
            ]
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    public ?string $code = null;


    #[ApiProperty(
        description: "The description of the NGAP",
        required: true,
        attributes: [
            "openapi_context" => [
                "type" => "string",
                "example" => "Autres accessoires traitement à domicile (Titre I Chapitre I de la LPP)"
            ]
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_START)]
    #[Groups(['read'])]
    #[ORM\Column(type: 'text', nullable: false)]
    public ?string $description = null;


    public function __toString(): string
    {
        return "$this->code - $this->description";
    }
}
