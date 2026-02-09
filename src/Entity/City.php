<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiPlatform\Filter\CityFilter;
use App\Doctrine\Types\PointType;
use App\Entity\Traits\ImportIdTrait;
use App\Repository\CityRepository;
use App\StateProvider\DefaultItemDataProvider;
use App\StateProvider\SimilarCitiesProvider;
use App\StateProvider\SubCitiesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ApiFilter(CityFilter::class, properties: ['search', 'latitude', 'exclude_subcities'])]
#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'city', indexes: [
    new ORM\Index(columns: ['postalCode'], name: 'idx_postal_code'),
    new ORM\Index(columns: ['name'], name: 'idx_name'),
    new ORM\Index(columns: ['rawName'], name: 'idx_raw_name'),
    new ORM\Index(columns: ['subCityAltName'], name: 'idx_sub_city_alt_name'),
    new ORM\Index(columns: ['inseeCode'], name: 'idx_insee_code'),
    new ORM\Index(columns: ['coordinates'], name: 'idx_coordinates'),
])]
#[ApiResource(
    shortName: 'City',
    operations: [
        new GetCollection(order: ['name' => 'ASC']),
        new Get(
            provider: DefaultItemDataProvider::class
        ),
        new GetCollection(
            uriTemplate: '/cities/{id}/sub_cities',
            normalizationContext: ['groups' => ['city:sub_cities:read']],
            provider: SubCitiesProvider::class
        ),
        new GetCollection(
            uriTemplate: '/cities/{id}/similar{._format}',
            provider: SimilarCitiesProvider::class,
        ),
    ],
    paginationClientEnabled: true,
    paginationPartial: true,
)]
/** @phpstan-ignore-next-line  */
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial'])]
/** @phpstan-ignore-next-line  */
#[ApiFilter(OrderFilter::class, properties: ['population' => 'DESC'], arguments: ['orderParameterName' => '_orderBy'])]
class City extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    #[Groups(['read'])]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $canonical = null;

    // Normalized name of the main city : Bourg-Saint-Christophe
    // With accents when we could match the data with the coordinates import file.
    // Expanded : ST DENIS => Saint-Denis
    #[Groups(['read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Original name from the CSV file - usually in uppercase without accents ex : BOURG ST CHRISTOPHE
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rawName = null;

    // Normalized sub-city name  ex : Marfoz
    #[Groups(['read', 'city:item:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subCityName = null;

    // Original sub-city name from the CSV file (usually libelle_5) ex : MARFOZ
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rawSubName = null;

    #[Groups(['city:item:read'])]
    #[ORM\Column(length: 8, nullable: false)]
    private ?string $inseeCode = null;

    #[Groups(['read'])]
    #[ORM\Column(length: 12)]
    private ?string $postalCode = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['city:item:read'])]
    private ?array $additionalPostalCodes = [];

    #[Groups(['city:item:read'])]
    #[ORM\ManyToOne(inversedBy: 'cities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Department $department = null;

    #[Groups(['read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 22, scale: 16, nullable: true)]
    private ?string $latitude = null;

    #[Groups(['read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 22, scale: 16, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(type: PointType::POINT, nullable: true)]
    private array $coordinates = [];

    #[Groups(['read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $population = null;

    #[Groups(['read'])]
    #[MaxDepth(1)]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'EXTRA_LAZY', inversedBy: 'subCities')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?self $mainCity = null;

    #[ORM\OneToMany(mappedBy: 'mainCity', targetEntity: self::class, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $subCities;

    public function __construct()
    {
        parent::__construct();
        $this->subCities = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSubCityName(): ?string
    {
        return $this->subCityName;
    }

    public function setSubCityName(?string $subCityName): void
    {
        $this->subCityName = $subCityName;
    }

    public function getRawName(): ?string
    {
        return $this->rawName;
    }

    public function setRawName(?string $rawName): void
    {
        $this->rawName = $rawName;
    }

    public function getRawSubName(): ?string
    {
        return $this->rawSubName;
    }

    public function setRawSubName(?string $rawSubName): void
    {
        $this->rawSubName = $rawSubName;
    }

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function setCanonical(string $canonical): static
    {
        $this->canonical = $canonical;

        return $this;
    }

    public function getLatitude(): ?string
    {
        if (isset($this->coordinates['latitude']) && $this->coordinates['latitude']) {
            return (string) $this->coordinates['latitude'];
        }

        if (!$this->latitude) {
            $subcity = $this->getSubCities()->first();

            return $subcity ? $subcity->getLatitude() : null;
        }

        return (string) $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        // Convert empty strings to null
        $this->latitude = $latitude;

        $this->coordinates = [
            'latitude' => $this->latitude ?? 0,
            'longitude' => $this->longitude ?? 0,
        ];

        return $this;
    }

    public function getLongitude(): ?string
    {
        if (isset($this->coordinates['longitude']) && $this->coordinates['longitude']) {
            return (string) $this->coordinates['longitude'];
        }

        if (!$this->latitude) {
            $subcity = $this->getSubCities()->first();

            return $subcity ? $subcity->getLongitude() : null;
        }

        return $this->longitude ? (string) $this->longitude : null;
    }

    public function setLongitude(?string $longitude): static
    {
        // Convert empty strings to null
        $this->longitude = $longitude;

        $this->coordinates = [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];

        return $this;
    }

    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getMainCity(): ?self
    {
        return $this->mainCity;
    }

    public function setMainCity(?self $mainCity): static
    {
        $this->mainCity = $mainCity;

        return $this;
    }

    /**
     * @return Collection<int, City>
     */
    public function getSubCities(): Collection
    {
        return $this->subCities;
    }

    public function addSubCity(self $subCity): static
    {
        if (!$this->subCities->contains($subCity)) {
            $this->subCities->add($subCity);
            $subCity->setMainCity($this);
        }

        return $this;
    }

    public function removeSubCity(self $subCity): static
    {
        if ($this->subCities->removeElement($subCity)) {
            // Set the owning side to null (unless already changed)
            if ($subCity->getMainCity() === $this) {
                $subCity->setMainCity(null);
            }
        }

        return $this;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): void
    {
        $this->inseeCode = $inseeCode;
    }

    public function getPopulation(): ?int
    {
        return $this->population;
    }

    public function setPopulation(?int $population): void
    {
        $this->population = $population;
    }

    public function getAdditionalPostalCodes(): ?array
    {
        return $this->additionalPostalCodes;
    }

    public function setAdditionalPostalCodes(?array $additionalPostalCodes): static
    {
        $this->additionalPostalCodes = $additionalPostalCodes;

        return $this;
    }

    public function addAdditionalPostalCode(string $postalCode): static
    {
        if (null === $this->additionalPostalCodes) {
            $this->additionalPostalCodes = [];
        }

        // Ensure that the postal code is not already in the array
        if (!in_array($postalCode, $this->additionalPostalCodes, true)) {
            $this->additionalPostalCodes[] = $postalCode;
        }

        return $this;
    }

    // Helper method to determine if this city is a main city
    public function isMainCity(): bool
    {
        return null === $this->mainCity;
    }

    // Helper method to determine if this city is a sub city
    public function isSubCity(): bool
    {
        return null !== $this->mainCity;
    }

    #[Groups(['read'])]
    public function getHasSubCities(): bool
    {
        return !$this->subCities->isEmpty();
    }

    #[Groups(['read'])]
    public function getRealName(): string
    {
        return $this->subCityName ?? $this->name;
    }

    // Returns the concatenated full city name
    public function getFullCityName(): string
    {
        if ($this->subCityName) {
            return $this->name . ' - ' . $this->subCityName;
        }

        return $this->name;
    }

    #[Groups(['read'])]
    public function getCityName(): string
    {
        return $this->subCityName ?? $this->name;
    }

    // Helper method to check if the city matches a given name or any of its alternative names
    public function matchesName(string $name): bool
    {
        return 0 === strcasecmp($this->name, $name)
            || 0 === strcasecmp($this->rawName, $name)
            || 0 === strcasecmp($this->subCityName, $name)
            || 0 === strcasecmp($this->rawSubName, $name);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
