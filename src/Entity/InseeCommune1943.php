<?php

namespace App\Entity;

use App\Entity\Traits\ImportIdTrait;
use App\Repository\InseeCommune1943Repository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InseeCommune1943Repository::class)]
#[ORM\Table(name: 'insee_commune_1943')]
class InseeCommune1943 extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    /**
     * COM (5 characters) : Commune code
     * Example : "01001".
     */
    #[ORM\Column(length: 5, nullable: true)]
    private ?string $codeCommune = null;

    /**
     * TNCC (1 character) : Type de nom en clair
     * Example : "5".
     */
    #[ORM\Column(length: 1, nullable: true)]
    private ?string $typeNomEnClair = null;

    /**
     * NCC (200 characters) : Nom en clair (majuscules)
     * Example: "ABERGEMENT CLEMENCIAT".
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomMajuscule = null;

    /**
     * NCCENR (200 characters) : Nom en clair (typographie riche)
     * Example: "Abergement-Clémenciat".
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomTypographie = null;

    /**
     * LIBELLE (200 characters) : Nom en clair (typographie riche) avec article
     * Example : "L'Abergement-Clémenciat".
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomAvecArticle = null;

    /**
     * DATE_DEBUT (YYYY-MM-DD) : Start date for the code-libellé pair
     * If empty => No known start date or historically from 1943-01-01.
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateDebut = null;

    /**
     * DATE_FIN (YYYY-MM-DD) : End date for the code-libellé pair
     * If empty => still active.
     */
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateFin = null;

    public function getCodeCommune(): ?string
    {
        return $this->codeCommune;
    }

    public function setCodeCommune(?string $codeCommune): void
    {
        $this->codeCommune = $codeCommune;
    }

    public function getTypeNomEnClair(): ?string
    {
        return $this->typeNomEnClair;
    }

    public function setTypeNomEnClair(?string $typeNomEnClair): void
    {
        $this->typeNomEnClair = $typeNomEnClair;
    }

    public function getNomMajuscule(): ?string
    {
        return $this->nomMajuscule;
    }

    public function setNomMajuscule(?string $nomMajuscule): void
    {
        $this->nomMajuscule = $nomMajuscule;
    }

    public function getNomTypographie(): ?string
    {
        return $this->nomTypographie;
    }

    public function setNomTypographie(?string $nomTypographie): void
    {
        $this->nomTypographie = $nomTypographie;
    }

    public function getNomAvecArticle(): ?string
    {
        return $this->nomAvecArticle;
    }

    public function setNomAvecArticle(?string $nomAvecArticle): void
    {
        $this->nomAvecArticle = $nomAvecArticle;
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
        return $this->nomMajuscule ?: 'Unknown Commune1943';
    }
}
