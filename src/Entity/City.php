<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ApiResource]
#[ORM\Table(name: 'city', indexes: [
    new ORM\Index(columns: ['postalCode'], name: 'idx_postal_code'),
    new ORM\Index(columns: ['altName'], name: 'idx_alt_name'),
    new ORM\Index(columns: ['subCityAltName'], name: 'idx_sub_city_alt_name'),
    new ORM\Index(columns: ['inseeCode'], name: 'idx_insee_code'),
])]
class City extends Thing implements Entity
{
    #[ORM\Column(length: 255, unique: true)]
    private ?string $canonical = null;

    // Normalized name of the main city : Bourg-Saint-Christophe
    // With accents when we could match the data with the coordinates import file.
    // Expanded : ST DENIS => Saint-Denis
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Original name from the CSV file - usually in uppercase without accents ex : BOURG ST CHRISTOPHE
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rawName = null;

    // Normalized sub-city name  ex : Marfoz
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subCityName = null;

    // Original sub-city name from the CSV file (usually libelle_5) ex : MARFOZ
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rawSubName = null;

    #[ORM\Column(length: 8, nullable: false)]
    private ?string $inseeCode = null;

    #[ORM\Column(length: 12)]
    private ?string $postalCode = null;

    #[ORM\ManyToOne(inversedBy: 'cities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 22, scale: 16, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 22, scale: 16, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $population = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subCities')]
    private ?self $mainCity = null;

    #[ORM\OneToMany(mappedBy: 'mainCity', targetEntity: self::class, cascade: ['persist', 'remove'])]
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
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        // Convert empty strings to null
        $this->latitude = $latitude === '' ? null : $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        // Convert empty strings to null
        $this->longitude = $longitude === '' ? null : $longitude;
        return $this;
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

    // Helper method to determine if this city is a main city
    public function isMainCity(): bool
    {
        return $this->mainCity === null;
    }

    // Helper method to determine if this city is a sub city
    public function isSubCity(): bool
    {
        return $this->mainCity !== null;
    }

    // Returns the concatenated full city name
    public function getFullCityName(): string
    {
        if ($this->subCityName) {
            return $this->name . ' - ' . $this->subCityName;
        }
        return $this->name;
    }

    // Helper method to check if the city matches a given name or any of its alternative names
    public function matchesName(string $name): bool
    {
        return strcasecmp($this->name, $name) === 0 ||
            strcasecmp($this->rawName, $name) === 0 ||
            strcasecmp($this->subCityName, $name) === 0 ||
            strcasecmp($this->rawSubName, $name) === 0;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
