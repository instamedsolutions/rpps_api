<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\RPPSRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Entity(repositoryClass=RPPSRepository::class)
 *
 * @ORM\Table(name="rpps",indexes={
 *     @ORM\Index(name="rpps_index", columns={"id_rpps"})
 * })
 *
 * @UniqueEntity("idRpps")
 *
 */
class RPPS
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     *
     * The unique RPPS identifier of the medic
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @ORM\Column(type="string", nullable=true,unique=true)
     */
    protected $idRpps;

    /**
     *
     * The civility of the user
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $specialty;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $zipcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $finessNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $cpsNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdRpps(): ?string
    {
        return $this->idRpps;
    }

    public function setIdRpps(?string $id_rpps): self
    {
        $this->idRpps = $id_rpps;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFinessNumber(): ?string
    {
        return $this->finessNumber;
    }

    public function setFinessNumber(?string $finessNumber): self
    {
        $this->finessNumber = $finessNumber;

        return $this;
    }

    public function getCpsNumber(): ?string
    {
        return $this->cpsNumber;
    }

    public function setCpsNumber(?string $cpsNumber): self
    {
        $this->cpsNumber = $cpsNumber;

        return $this;
    }
}
