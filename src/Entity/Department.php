<?php

namespace App\Entity;

use App\Enum\DepartmentType;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department extends Thing implements Entity
{
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $codeDepartment = null;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: City::class)]
    private Collection $cities;

    #[ORM\ManyToOne(inversedBy: 'departments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Region $region = null;

    #[ORM\OneToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?City $chefLieu = null;

    #[ORM\Column(type: 'string', enumType: DepartmentType::class)]
    private DepartmentType $departmentType;

    // Temporary variable to hold the chef-lieu name during import
    // This variable is not persisted in the database and is used only during data processing
    public ?string $tempChefLieuName = null;

    public function __construct()
    {
        parent::__construct();
        $this->cities = new ArrayCollection();
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

    public function getCodeDepartment(): ?string
    {
        return $this->codeDepartment;
    }

    public function setCodeDepartment(string $codeDepartment): static
    {
        $this->codeDepartment = $codeDepartment;
        return $this;
    }

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setDepartment($this);
        }
        return $this;
    }

    public function removeCity(City $city): static
    {
        if ($this->cities->removeElement($city)) {
            if ($city->getDepartment() === $this) {
                $city->setDepartment(null);
            }
        }
        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;
        return $this;
    }

    public function getChefLieu(): ?City
    {
        return $this->chefLieu;
    }

    public function setChefLieu(?City $chefLieu): static
    {
        $this->chefLieu = $chefLieu;
        return $this;
    }

    public function getDepartmentType(): DepartmentType
    {
        return $this->departmentType;
    }

    public function setDepartmentType(DepartmentType $departmentType): static
    {
        $this->departmentType = $departmentType;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (%s) - Chef-Lieu: %s',
            $this->getCodeDepartment(),
            $this->getName(),
            $this->getDepartmentType()->value,
            $this->chefLieu ? $this->chefLieu->getName() : 'N/A'
        );
    }
}
