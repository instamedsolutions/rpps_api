<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiPlatform\Filter\SpecialtyFilter;
use App\Repository\SpecialtyRepository;
use App\StateProvider\DefaultItemDataProvider;
use App\StateProvider\SimilarSpecialtiesProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SpecialtyRepository::class)]
#[ORM\Index(columns: ['name'], name: 'specialty_name_index')]
#[ORM\Index(columns: ['is_paramedical', 'id'], name: 'idx_specialty_composite')]
#[ORM\Index(columns: ['is_paramedical'], name: 'specialty_is_paramedical_index')]
#[ApiResource(
    shortName: 'Specialty',
    operations: [
        new GetCollection(order: ['name' => 'ASC']),
        new Get(provider: DefaultItemDataProvider::class),
        new Get(
            uriTemplate: '/specialties/{id}/similar{._format}',
            openapiContext: [
                'summary' => 'Get similar specialties linked to this specialty',
                'description' => 'Returns a list of specialties linked via the specialties field',
            ],
            name: 'get_similar_specialties',
            provider: SimilarSpecialtiesProvider::class,
        ),
    ],
)]
#[ApiFilter(SpecialtyFilter::class, properties: ['search', 'by_rpps', 'excluded_specialties'])]
class Specialty extends Thing implements Entity
{
    #[Groups(['read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['read'])]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $canonical = null;

    #[Groups(['read'])]
    #[ORM\Column(length: 255)]
    private ?string $specialistName = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isParamedical = false;

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

    public function getSpecialistName(): ?string
    {
        return $this->specialistName;
    }

    public function setSpecialistName(?string $specialistName): void
    {
        $this->specialistName = $specialistName;
    }

    public function isParamedical(): bool
    {
        return $this->isParamedical;
    }

    public function setIsParamedical(bool $isParamedical): void
    {
        $this->isParamedical = $isParamedical;
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
