<?php

namespace App\Entity;

use App\Entity\Traits\ImportIdTrait;
use App\Repository\InseeCommuneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InseeCommuneRepository::class)]
#[ORM\Table(name: 'insee_commune')]
class InseeCommune extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    /**
     * TYPECOM (4 caractères) : Type de commune.
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $typeCommune = null;

    /**
     * COM (5 caractères) : Code commune.
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeCommune = null;

    /**
     * REG (2 caractères) : Code région.
     */
    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codeRegion = null;

    /**
     * DEP (3 caractères) : Code département.
     */
    #[ORM\Column(length: 3, nullable: true)]
    private ?string $codeDepartement = null;

    /**
     * CTCD (4 caractères) : Code de la collectivité territoriale (compétences départementales).
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $codeCollectivite = null;

    /**
     * ARR (4 caractères) : Code arrondissement.
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $codeArrondissement = null;

    /**
     * TNCC (1 caractère) : Type de nom en clair.
     */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $typeNomEnClair = null;

    /**
     * NCC (200 caractères) : Nom en clair (majuscules).
     */
    #[ORM\Column(length: 200)]
    private ?string $nomEnClair = null;

    /**
     * NCCENR (200 caractères) : Nom en clair (typographie riche).
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomEnClairTypo = null;

    /**
     * LIBELLE (200 caractères) : Nom en clair (typographie riche) avec article.
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomEnClairAvecArticle = null;

    /**
     * CAN (5 caractères) : Code canton.
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeCanton = null;

    /**
     * COMPARENT (5 caractères) : Code de la commune parente.
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeCommuneParente = null;

    public function getTypeCommune(): ?string
    {
        return $this->typeCommune;
    }

    public function setTypeCommune(?string $typeCommune): void
    {
        $this->typeCommune = $typeCommune;
    }

    public function getCodeCommune(): ?string
    {
        return $this->codeCommune;
    }

    public function setCodeCommune(?string $codeCommune): void
    {
        $this->codeCommune = $codeCommune;
    }

    public function getCodeRegion(): ?string
    {
        return $this->codeRegion;
    }

    public function setCodeRegion(?string $codeRegion): void
    {
        $this->codeRegion = $codeRegion;
    }

    public function getCodeDepartement(): ?string
    {
        return $this->codeDepartement;
    }

    public function setCodeDepartement(?string $codeDepartement): void
    {
        $this->codeDepartement = $codeDepartement;
    }

    public function getCodeCollectivite(): ?string
    {
        return $this->codeCollectivite;
    }

    public function setCodeCollectivite(?string $codeCollectivite): void
    {
        $this->codeCollectivite = $codeCollectivite;
    }

    public function getCodeArrondissement(): ?string
    {
        return $this->codeArrondissement;
    }

    public function setCodeArrondissement(?string $codeArrondissement): void
    {
        $this->codeArrondissement = $codeArrondissement;
    }

    public function getTypeNomEnClair(): ?string
    {
        return $this->typeNomEnClair;
    }

    public function setTypeNomEnClair(?string $typeNomEnClair): void
    {
        $this->typeNomEnClair = $typeNomEnClair;
    }

    public function getNomEnClair(): ?string
    {
        return $this->nomEnClair;
    }

    public function setNomEnClair(?string $nomEnClair): void
    {
        $this->nomEnClair = $nomEnClair;
    }

    public function getNomEnClairTypo(): ?string
    {
        return $this->nomEnClairTypo;
    }

    public function setNomEnClairTypo(?string $nomEnClairTypo): void
    {
        $this->nomEnClairTypo = $nomEnClairTypo;
    }

    public function getNomEnClairAvecArticle(): ?string
    {
        return $this->nomEnClairAvecArticle;
    }

    public function setNomEnClairAvecArticle(?string $nomEnClairAvecArticle): void
    {
        $this->nomEnClairAvecArticle = $nomEnClairAvecArticle;
    }

    public function getCodeCanton(): ?string
    {
        return $this->codeCanton;
    }

    public function setCodeCanton(?string $codeCanton): void
    {
        $this->codeCanton = $codeCanton;
    }

    public function getCodeCommuneParente(): ?string
    {
        return $this->codeCommuneParente;
    }

    public function setCodeCommuneParente(?string $codeCommuneParente): void
    {
        $this->codeCommuneParente = $codeCommuneParente;
    }

    public function __toString(): string
    {
        return $this->getNomEnClair();
    }
}
