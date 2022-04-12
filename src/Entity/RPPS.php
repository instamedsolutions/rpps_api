<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\RPPSFilter;
use App\Repository\RPPSRepository;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass=RPPSRepository::class)
 *
 * @ORM\Table(name="rpps",indexes={
 *     @ORM\Index(name="rpps_index", columns={"id_rpps"})
 * })
 *
 * @ApiFilter(RPPSFilter::class,properties={"search","demo"})
 *
 *
 * @UniqueEntity("idRpps")
 *
 */
class RPPS extends Thing implements Entity
{

    /**
     *
     * @Groups({"read"})
     *
     * The unique RPPS identifier of the medic
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *             "example"="810003820189"
     *         }
     *     }
     * )
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @ORM\Column(type="string", nullable=true,unique=true)
     */
    protected $idRpps;

    /**
     *
     * The civility of the doctor
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Docteur"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     *
     * The last name of the doctor
     *
     * @Groups({"read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="RENE"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Marc"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Médecin"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $specialty;

    /**
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="12 Rue de Paris"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="75019"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $zipcode;

    /**
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Paris"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     *
     * @Groups({"read"})
     *
     * @AssertPhoneNumber(defaultRegion="FR")
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="+33144955555"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="phone_number",nullable=true)
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="jean.doe@free.fr"
     *         }
     *     }
     * )
     *
     */
    protected $email;

    /**
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="740787791"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $finessNumber;

    /**
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="2800089831"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $cpsNumber;


    /**
     * @return string|null
     */
    public function getIdRpps(): ?string
    {
        return $this->idRpps;
    }


    /**
     * @param string|null $id_rpps
     * @return $this
     */
    public function setIdRpps(?string $id_rpps): self
    {
        $this->idRpps = $id_rpps;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }


    /**
     * @param string|null $title
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return mb_convert_case($this->lastName,MB_CASE_UPPER);
    }


    /**
     * @param string|null $lastName
     * @return $this
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return mb_convert_case($this->firstName,MB_CASE_TITLE);
    }


    /**
     * @param string|null $firstName
     * @return $this
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }


    /**
     * @param string|null $specialty
     * @return $this
     */
    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        $address = trim($this->address);
        $address = preg_replace("# {2,}#"," ",$address);

        return $address ? $address : null;
    }

    /**
     * @param string|null $address
     * @return self
     */
    public function setAddress(?string $address): self
    {
        $address = preg_replace("# {2,}#"," ",$address);

        $address = trim($address);
        if($address) {
            $this->address = $address;
        } else {
            $address = null;
        }


        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
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
        return trim(preg_replace("#^[0-9]{5,6}#",'',$this->city));
    }

    public function setCity(?string $city): self
    {
        $city = trim(preg_replace("#^[0-9]{5,6}#",'',$city));

        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }


    /**
     * @param string|PhoneNumber|null $number
     * @return $this
     */
    public function setPhoneNumber($number): self
    {
        if(!$number) {
            $this->phoneNumber = null;
            return $this;
        }

        if(is_string($number)) {
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();

                $region = strpos($number, "+") === false ? "FR" : PhoneNumberUtil::UNKNOWN_REGION;

                $number = $phoneUtil->parse($number, $region);
            }catch (\Exception $exception) {
                $number = null;
            }
        }

        $this->phoneNumber = $number;

        return $this;
    }

    public function getEmail(): ?string
    {
        if(!$this->email) {
            return null;
        }
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        if(!$email) {
            $email = null;
        }
        $this->email = $email;

        return $this;
    }

    public function getFinessNumber(): ?string
    {
        if(!$this->finessNumber) {
           return null;
        }
        return $this->finessNumber;
    }

    public function setFinessNumber(?string $finessNumber): self
    {
        if(!$finessNumber) {
            $finessNumber = null;
        }
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


    /**
     *
     * @Groups({"read"})
     *
     * @return string
     */
    public function getFullName() : string
    {

        return trim("{$this->shortTitle()} {$this->getFirstName()} {$this->getLastName()}");

    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getFullName();
    }


    /**
     * @return string|null
     */
    protected function shortTitle() : ?string
    {
        switch ($this->title)
        {
            case "Docteur" :
                return "Dr.";
            case "Professeur":
                return "Pr.";
            case "Madame":
                return "Mme";
            case "Monsieur":
                return "M.";
            default:
                return null;
        }
    }


}
