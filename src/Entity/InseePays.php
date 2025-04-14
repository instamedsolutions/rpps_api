<?php

namespace App\Entity;

use App\Entity\Traits\ImportIdTrait;
use App\Repository\InseePaysRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InseePaysRepository::class)]
#[ORM\Table(name: 'insee_pays')]
class InseePays extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    /**
     * COG (5 caractères) : Code du pays ou territoire.
     *
     * Example : "99100"
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codePays = null;

    /**
     * ACTUAL (1 caractère) : Code actualité du pays/territoire.
     *
     * Example : "1"
     */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $codeActualite = null;

    /**
     * CRPAY (5 caractères) : Code officiel géographique du pays de rattachement.
     *
     * Example : Could be "99351", etc.
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeRattachement = null;

    /**
     * ANI (4 caractères) : Année d'apparition du code au COG.
     *
     * Example : "1993"
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $anneeApparition = null;

    /**
     * LIBCOG (70 caractères) : Libellé utilisé dans le COG.
     *
     * Example : "France"
     */
    #[ORM\Column(length: 70, nullable: true)]
    private ?string $libelleCog = null;

    /**
     * LIBENR (200 caractères) : Nom officiel ou composition détaillée.
     *
     * Example : "République française"
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $libelleOfficiel = null;

    /**
     * CODEISO2 (2 caractères) : Code pays ISO 3166-1 alpha-2.
     *
     * Example: "FR"
     */
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codeIso2 = null;

    /**
     * CODEISO3 (3 caractères) : Code pays ISO 3166-1 alpha-3.
     *
     * Example : "FRA"
     */
    #[ORM\Column(length: 3, nullable: true)]
    private ?string $codeIso3 = null;

    /**
     * CODENUM3 (3 caractères) : Code pays ISO 3166-1 numérique.
     *
     * Example : "250"
     */
    #[ORM\Column(length: 3, nullable: true)]
    private ?string $codeIsoNum3 = null;

    public function getCodePays(): ?string
    {
        return $this->codePays;
    }

    public function setCodePays(?string $codePays): void
    {
        $this->codePays = $codePays;
    }

    public function getCodeActualite(): ?string
    {
        return $this->codeActualite;
    }

    public function setCodeActualite(?string $codeActualite): void
    {
        $this->codeActualite = $codeActualite;
    }

    public function getCodeRattachement(): ?string
    {
        return $this->codeRattachement;
    }

    public function setCodeRattachement(?string $codeRattachement): void
    {
        $this->codeRattachement = $codeRattachement;
    }

    public function getAnneeApparition(): ?string
    {
        return $this->anneeApparition;
    }

    public function setAnneeApparition(?string $anneeApparition): void
    {
        $this->anneeApparition = $anneeApparition;
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

    public function getCodeIso2(): ?string
    {
        return $this->codeIso2;
    }

    public function setCodeIso2(?string $codeIso2): void
    {
        $this->codeIso2 = $codeIso2;
    }

    public function getCodeIso3(): ?string
    {
        return $this->codeIso3;
    }

    public function setCodeIso3(?string $codeIso3): void
    {
        $this->codeIso3 = $codeIso3;
    }

    public function getCodeIsoNum3(): ?string
    {
        return $this->codeIsoNum3;
    }

    public function setCodeIsoNum3(?string $codeIsoNum3): void
    {
        $this->codeIsoNum3 = $codeIsoNum3;
    }

    public function __toString(): string
    {
        return $this->getLibelleCog() ?: 'Unknown Country';
    }
}
