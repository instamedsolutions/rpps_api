<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\RPPSFilter;
use App\Repository\RPPSRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiFilter(RPPSFilter::class, properties: ['search', 'demo'])]
#[ORM\Entity(repositoryClass: RPPSRepository::class)]
#[ORM\Table(name: 'rpps')]
#[ORM\Index(name: 'rpps_index', columns: ['id_rpps'])]
#[UniqueEntity('idRpps')]
class RPPS extends Thing implements Entity, Stringable
{
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ApiProperty(description: 'The unique RPPS identifier of the medic', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '810003820189',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    protected ?string $idRpps = null;

    #[ApiProperty(description: 'The civility of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'Docteur',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $title = null;

    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ApiProperty(description: 'The last name of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'RENE',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $lastName = null;

    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ApiProperty(description: 'The first name of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'Marc',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $firstName = null;

    #[ApiProperty(description: 'The specialty of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'Médecin',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $specialty = null;

    #[ApiProperty(description: 'The address of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '12 Rue de Paris',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $address = null;

    #[ApiProperty(description: 'The postal code of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '75019',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $zipcode = null;

    #[ApiProperty(description: 'The city of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'Paris',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $city = null;

    #[ApiProperty(description: 'The phone number of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '+33144955555',
        ],
    ])]
    #[AssertPhoneNumber(defaultRegion: 'FR')]
    #[Groups(['read'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    protected ?PhoneNumber $phoneNumber = null;

    #[ApiProperty(description: 'The email of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => 'jean.doe@free.fr',
        ],
    ])]
    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read'])]
    protected ?string $email = null;

    #[ApiProperty(description: 'The Finess number of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '740787791',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $finessNumber = null;

    #[ApiProperty(description: 'The CPS number of the doctor', required: false, attributes: [
        'openapi_context' => [
            'type' => 'string',
            'example' => '2800089831',
        ],
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $cpsNumber = null;

    public function getIdRpps(): ?string
    {
        return $this->idRpps;
    }

    /**
     * @return $this
     */
    public function setIdRpps(?string $id_rpps): self
    {
        $this->idRpps = $id_rpps;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLastName(): ?string
    {
        return mb_convert_case((string) $this->lastName, MB_CASE_UPPER);
    }

    /**
     * @return $this
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return mb_convert_case((string) $this->firstName, MB_CASE_TITLE);
    }

    /**
     * @return $this
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    /**
     * @return $this
     */
    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    public function getAddress(): ?string
    {
        $address = trim((string) $this->address);
        $address = preg_replace('# {2,}#', ' ', $address);

        return $address ?: null;
    }

    public function setAddress(?string $address): self
    {
        $address = preg_replace('# {2,}#', ' ', $address);

        $address = trim($address);
        if ('' !== $address && '0' !== $address) {
            $this->address = $address;
        } else {
            $address = null;
        }

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
        if (!$this->city) {
            return null;
        }

        return trim(preg_replace('#^\\d{5,6}#', '', $this->city));
    }

    public function setCity(?string $city): self
    {
        $city = trim(preg_replace('#^\\d{5,6}#', '', $city));

        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|PhoneNumber|null $number
     *
     * @return $this
     */
    public function setPhoneNumber($number): self
    {
        if (!$number) {
            $this->phoneNumber = null;

            return $this;
        }

        if (is_string($number)) {
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();

                $region = str_contains($number, '+') ? PhoneNumberUtil::UNKNOWN_REGION : 'FR';

                $number = $phoneUtil->parse($number, $region);
            } catch (Exception) {
                $number = null;
            }
        }

        $this->phoneNumber = $number;

        return $this;
    }

    public function getEmail(): ?string
    {
        if (!$this->email) {
            return null;
        }

        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        if (!$email) {
            $email = null;
        }
        $this->email = $email;

        return $this;
    }

    public function getFinessNumber(): ?string
    {
        if (!$this->finessNumber) {
            return null;
        }

        return $this->finessNumber;
    }

    public function setFinessNumber(?string $finessNumber): self
    {
        if (!$finessNumber) {
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

    #[Groups(['read'])]
    public function getFullName(): string
    {
        return trim((string) "{$this->shortTitle()} {$this->getFirstName()} {$this->getLastName()}");
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    protected function shortTitle(): ?string
    {
        return match ($this->title) {
            'Docteur' => 'Dr.',
            'Professeur' => 'Pr.',
            'Madame' => 'Mme',
            'Monsieur' => 'M.',
            default => null,
        };
    }
}
