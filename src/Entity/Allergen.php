<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiPlatform\Filter\AllergenFilter;
use App\Entity\Traits\ImportIdTrait;
use App\Entity\Traits\TranslatableTrait;
use App\Repository\AllergenRepository;
use App\StateProvider\DefaultItemDataProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

// Liste extracted from
// https://biologiepathologie.chu-lille.fr/fichiers/42_795catalogue-rast-i.pdf

#[ApiFilter(AllergenFilter::class, properties: ['search'])]
#[ORM\Entity(repositoryClass: AllergenRepository::class)]
#[ORM\Table(name: 'allergens')]
#[ORM\Index(columns: ['allergen_code'], name: 'allergens_index')]
#[UniqueEntity('code')]
#[ApiResource(
    shortName: 'allergen',
    operations: [
        new GetCollection(
            order: ['name' => 'ASC'],
        ),
        new Get(
            provider: DefaultItemDataProvider::class
        ),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
class Allergen extends BaseEntity implements TranslatableEntityInterface
{
    use ImportIdTrait;
    use TranslatableTrait;

    #[ApiProperty(
        description: 'The unique code of the allergen',
        required: true,
        openapiContext: [
            'type' => 'string',
            'example' => '01',
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[Groups(['read'])]
    #[ORM\Column(name: 'allergen_code', type: 'string', length: 10, unique: true)]
    protected ?string $code = null;

    #[ApiProperty(
        description: 'The name of the allergen',
        required: true,
        openapiContext: [
            'type' => 'string',
            'example' => 'Corn',
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name = null;

    #[ApiProperty(
        description: 'The parent group of the allergen',
        required: true,
        openapiContext: [
            'type' => 'string',
            'example' => 'Pollens de graminées',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(name: 'allergen_group', type: 'string', length: 255)]
    protected ?string $group = null;

    public function __construct()
    {
        parent::__construct();
        $this->translations = new ArrayCollection();
    }

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

    public function __toString(): string
    {
        return (string) $this->getName();
    }
}
