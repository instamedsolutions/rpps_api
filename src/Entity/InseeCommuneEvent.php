<?php

namespace App\Entity;

use App\Entity\Traits\ImportIdTrait;
use App\Repository\InseeCommuneEventRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InseeCommuneEventRepository::class)]
#[ORM\Table(name: 'insee_commune_event')]
class InseeCommuneEvent extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    /**
     * MOD (2 characters) : Type of commune event
     * Example: "32", "33", "10", "35", etc.
     */
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $modEvent = null;  //  'mod' is MySql keyword

    /**
     * DATE_EFF (YYYY-MM-DD) : Effective date of the event.
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateEff = null;

    // ---- BEFORE EVENT ----

    /**
     * TYPECOM_AV (4 characters) : Type of commune before the event
     * Example: "COM", "COMA", "COMD".
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $typeCommuneAv = null;

    /**
     * COM_AV (5 characters) : Commune code before the event.
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeCommuneAv = null;

    /**
     * TNCC_AV (1 character) : Type de nom en clair (before event).
     */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $tnccAv = null;

    /**
     * NCC_AV (200 characters) : Nom en clair (majuscules) before event.
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomMajusculeAv = null;

    /**
     * NCCENR_AV (200 characters) : Typographic name before event.
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomTypoAv = null;

    /**
     * LIBELLE_AV (200 characters) : Typographic name with article (before event).
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomArticleAv = null;

    // ---- AFTER EVENT ----

    /**
     * TYPECOM_AP (4 characters) : Type of commune after the event
     * Example: "COM", "COMA", "COMD".
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $typeCommuneAp = null;

    /**
     * COM_AP (5 characters) : Commune code after the event.
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeCommuneAp = null;

    /**
     * TNCC_AP (1 character) : Type de nom en clair (after event).
     */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $tnccAp = null;

    /**
     * NCC_AP (200 characters) : Nom en clair (majuscules) after event.
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomMajusculeAp = null;

    /**
     * NCCENR_AP (200 characters) : Typographic name after event.
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomTypoAp = null;

    /**
     * LIBELLE_AP (200 characters) : Typographic name with article (after event).
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomArticleAp = null;

    public function getModEvent(): ?string
    {
        return $this->modEvent;
    }

    public function setModEvent(?string $modEvent): void
    {
        $this->modEvent = $modEvent;
    }

    public function getDateEff(): ?DateTimeInterface
    {
        return $this->dateEff;
    }

    public function setDateEff(?DateTimeInterface $dateEff): void
    {
        $this->dateEff = $dateEff;
    }

    public function getTypeCommuneAv(): ?string
    {
        return $this->typeCommuneAv;
    }

    public function setTypeCommuneAv(?string $typeCommuneAv): void
    {
        $this->typeCommuneAv = $typeCommuneAv;
    }

    public function getCodeCommuneAv(): ?string
    {
        return $this->codeCommuneAv;
    }

    public function setCodeCommuneAv(?string $codeCommuneAv): void
    {
        $this->codeCommuneAv = $codeCommuneAv;
    }

    public function getTnccAv(): ?string
    {
        return $this->tnccAv;
    }

    public function setTnccAv(?string $tnccAv): void
    {
        $this->tnccAv = $tnccAv;
    }

    public function getNomMajusculeAv(): ?string
    {
        return $this->nomMajusculeAv;
    }

    public function setNomMajusculeAv(?string $nomMajusculeAv): void
    {
        $this->nomMajusculeAv = $nomMajusculeAv;
    }

    public function getNomTypoAv(): ?string
    {
        return $this->nomTypoAv;
    }

    public function setNomTypoAv(?string $nomTypoAv): void
    {
        $this->nomTypoAv = $nomTypoAv;
    }

    public function getNomArticleAv(): ?string
    {
        return $this->nomArticleAv;
    }

    public function setNomArticleAv(?string $nomArticleAv): void
    {
        $this->nomArticleAv = $nomArticleAv;
    }

    public function getTypeCommuneAp(): ?string
    {
        return $this->typeCommuneAp;
    }

    public function setTypeCommuneAp(?string $typeCommuneAp): void
    {
        $this->typeCommuneAp = $typeCommuneAp;
    }

    public function getCodeCommuneAp(): ?string
    {
        return $this->codeCommuneAp;
    }

    public function setCodeCommuneAp(?string $codeCommuneAp): void
    {
        $this->codeCommuneAp = $codeCommuneAp;
    }

    public function getTnccAp(): ?string
    {
        return $this->tnccAp;
    }

    public function setTnccAp(?string $tnccAp): void
    {
        $this->tnccAp = $tnccAp;
    }

    public function getNomMajusculeAp(): ?string
    {
        return $this->nomMajusculeAp;
    }

    public function setNomMajusculeAp(?string $nomMajusculeAp): void
    {
        $this->nomMajusculeAp = $nomMajusculeAp;
    }

    public function getNomTypoAp(): ?string
    {
        return $this->nomTypoAp;
    }

    public function setNomTypoAp(?string $nomTypoAp): void
    {
        $this->nomTypoAp = $nomTypoAp;
    }

    public function getNomArticleAp(): ?string
    {
        return $this->nomArticleAp;
    }

    public function setNomArticleAp(?string $nomArticleAp): void
    {
        $this->nomArticleAp = $nomArticleAp;
    }

    public function __toString(): string
    {
        return 'Event ' . ($this->modEvent ?: '') . ' - ' . ($this->nomMajusculeAv ?: 'Unknown');
    }
}
