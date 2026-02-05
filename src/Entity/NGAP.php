<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiPlatform\Filter\NGAPFilter;
use App\Entity\Traits\ImportIdTrait;
use App\Repository\NGAPRepository;
use App\StateProvider\DefaultItemDataProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

// https://www.ameli.fr/sites/default/files/Documents/NGAP_%2025072022.pdf
#[ApiFilter(NGAPFilter::class, properties: ['search'])]
#[ORM\Entity(repositoryClass: NGAPRepository::class)]
#[ORM\Table(name: 'ngap')]
#[ORM\Index(columns: ['code'], name: 'ngap_index')]
#[UniqueEntity('code')]
#[ApiResource(
    shortName: 'Ngap',
    operations: [
        new GetCollection(),
        new Get(
            provider: DefaultItemDataProvider::class
        ),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
class NGAP extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    #[ApiProperty(
        description: 'The uniq code of the NGAP',
        required: true,
        schema: ['type' => 'string', 'example' => 'AAD'],
    )]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilterInterface::STRATEGY_EXACT)]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true)]
    public ?string $code = null;

    #[ApiProperty(
        description: 'The description of the NGAP',
        required: true,
        schema: ['type' => 'string', 'example' => 'Autres accessoires traitement à domicile (Titre I Chapitre I de la LPP)'],
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
