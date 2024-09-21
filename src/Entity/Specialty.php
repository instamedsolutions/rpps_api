<?php

namespace App\Entity;

use App\Repository\SpecialtyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecialtyRepository::class)]
#[ORM\Index(columns: ['name'], name: 'specialty_name_index')]
class Specialty extends Thing implements Entity
{
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $canonical = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'specialties')]
    #[ORM\JoinTable(name: 'specialty_links')]
    private Collection $specialties;

    public function __construct()
    {
        parent::__construct();
        $this->specialties = new ArrayCollection();
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

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function setCanonical(string $canonical): static
    {
        $this->canonical = $canonical;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSpecialties(): Collection
    {
        return $this->specialties;
    }

    public function addSpecialty(self $specialty): static
    {
        if (!$this->specialties->contains($specialty)) {
            $this->specialties->add($specialty);
        }
        return $this;
    }

    public function removeSpecialty(self $specialty): static
    {
        $this->specialties->removeElement($specialty);
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
