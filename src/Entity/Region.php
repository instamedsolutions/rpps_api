<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\ImportIdTrait;
use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ApiResource]
class Region extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    #[Groups(['read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['read'])]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $codeRegion = null;

    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Department::class)]
    private Collection $departments;

    public function __construct()
    {
        parent::__construct();
        $this->departments = new ArrayCollection();
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

    public function getCodeRegion(): ?string
    {
        return $this->codeRegion;
    }

    public function setCodeRegion(string $codeRegion): static
    {
        $this->codeRegion = $codeRegion;

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setRegion($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getRegion() === $this) {
                $department->setRegion(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getCodeRegion() . '-' . $this->getName();
    }
}
