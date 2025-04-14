<?php

namespace App\Entity;

use App\Entity\Traits\ImportIdTrait;
use App\Repository\InseePays1943Repository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InseePays1943Repository::class)]
#[ORM\Table(name: 'insee_pays_1943')]
class InseePays1943 extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    /**
     * COG (5 characters): Code of the country or territory.
     *
     * Example: "99102"
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codePays = null;

    /**
     * CRPAY (5 characters): Code of the currently attached country (if any).
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeRattachement = null;

    /**
     * LIBCOG (70 characters): Label used in the COG.
     *
     * Example: "Islande"
     */
    #[ORM\Column(length: 70, nullable: true)]
    private ?string $libelleCog = null;

    /**
     * LIBENR (200 characters): Official name or detailed composition of the territory.
     *
     * Example: "Royaume d’Islande"
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $libelleOfficiel = null;

    /**
     * DATE_DEBUT (10 characters): Start date of the code-country pair (YYYY-MM-DD).
     * If empty, this record was valid from at least 1943-01-01 or an unknown start date.
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateDebut = null;

    /**
     * DATE_FIN (10 characters): End date of the code-country pair (YYYY-MM-DD).
     * If empty, the pair is still active.
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateFin = null;

    public function getCodePays(): ?string
    {
        return $this->codePays;
    }

    public function setCodePays(?string $codePays): void
    {
        $this->codePays = $codePays;
    }

    public function getCodeRattachement(): ?string
    {
        return $this->codeRattachement;
    }

    public function setCodeRattachement(?string $codeRattachement): void
    {
        $this->codeRattachement = $codeRattachement;
    }

    public function getLibelleCog(): ?string
    {
        return $this->libelleCog;
    }

    public function setLibelleCog(?string $libelleCog): void
    {
        $this->libelleCog = $libelleCog;
    }

    public function getLibelleOfficiel(): ?string
    {
        return $this->libelleOfficiel;
    }

    public function setLibelleOfficiel(?string $libelleOfficiel): void
    {
        $this->libelleOfficiel = $libelleOfficiel;
    }

    public function getDateDebut(): ?DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?DateTimeInterface $dateDebut): void
    {
        $this->dateDebut = $dateDebut;
    }

    public function getDateFin(): ?DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?DateTimeInterface $dateFin): void
    {
        $this->dateFin = $dateFin;
    }

    public function __toString(): string
    {
        return $this->libelleCog ?: 'Unknown Country';
    }
}
