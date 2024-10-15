<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiPlatform\Filter\RPPSFilter;
use App\Repository\RPPSRepository;
use App\StateProvider\DefaultItemDataProvider;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

// TODO - remove this index when the migration to specialtyEntity is done.  @Bastien
#[ORM\Index(columns: ['specialty'], name: 'specialty_index')]
#[ApiFilter(RPPSFilter::class, properties: ['search', 'first_letter', 'city', 'specialty', 'demo', 'excluded_rpps'])]
#[ORM\Entity(repositoryClass: RPPSRepository::class)]
#[ORM\Table(name: 'rpps')]
#[ORM\Index(columns: ['last_name'], name: 'last_name_index')]
#[ORM\Index(columns: ['full_name'], name: 'full_name_index')]
#[ORM\Index(columns: ['full_name_inversed'], name: 'full_name_inversed_index')]
#[ORM\Index(columns: ['id_rpps'], name: 'rpps_index')]
#[ORM\Index(columns: ['canonical'], name: 'canonical_index')]
#[UniqueEntity('idRpps')]
#[ApiResource(
    shortName: 'Rpps',
    operations: [
        new GetCollection(
            order: ['lastName' => 'ASC'],
        ),
        new Get(
            provider: DefaultItemDataProvider::class
        ),
    ],
)]
class RPPS extends Thing implements Entity, Stringable
{
    #[ApiProperty(
        description: 'A unique canonical identifier for the doctor, based on name and address',
        required: false,
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: false)]
    private ?string $canonical = null;

    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[ApiProperty(
        description: 'The unique RPPS identifier of the medic',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => '810003820189',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    protected ?string $idRpps = null;

    #[ApiProperty(
        description: 'The civility of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'Docteur',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $title = null;

    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ApiProperty(
        description: 'The last name of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'RENE',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $lastName = null;

    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ApiProperty(
        description: 'The first name of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'Marc',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $firstName = null;

    /**
     * @deprecated use $specialtyEntity instead
     */
    #[ApiProperty(
        description: 'Deprecated. The specialty of the doctor. Use specialtyEntity instead.',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'Médecin',
            'deprecated' => true,
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $specialty = null;

    #[ApiProperty(
        description: 'The specialty entity of the doctor',
        required: false,
    )]
    #[Groups(['read'])]
    #[ORM\ManyToOne(targetEntity: Specialty::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Specialty $specialtyEntity = null;

    #[ApiProperty(
        description: 'The address of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => '12 Rue de Paris',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $address = null;

    #[ApiProperty(
        description: 'The address extension of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'BP 75',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $addressExtension = null;

    #[ApiProperty(
        description: 'The postal code of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => '75019',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $zipcode = null;

    /**
     * @deprecated use $cityEntity instead
     */
    #[ApiProperty(
        description: 'Deprecated. The city of the doctor, use cityEntity instead.',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'Paris',
            'deprecated' => true,
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $city = null;

    #[ApiProperty(
        description: 'The latitude of the doctor',
        required: false,
        openapiContext: [
            'type' => 'number',
            'example' => 48.8566,
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $latitude = null;

    #[ApiProperty(
        description: 'The latitude of the doctor',
        required: false,
        openapiContext: [
            'type' => 'number',
            'example' => 48.8566,
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $longitude = null;

    #[ApiProperty(
        description: 'The city entity of the doctor, with more detailed information such as population and coordinates.',
        required: false,
    )]
    #[Groups(['read'])]
    #[ORM\ManyToOne(targetEntity: City::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?City $cityEntity = null;

    #[ApiProperty(
        description: 'The phone number of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => '+33144955555',
        ]
    )]
    #[AssertPhoneNumber(defaultRegion: 'FR')]
    #[Groups(['read'])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    protected ?PhoneNumber $phoneNumber = null;

    #[ApiProperty(
        description: 'The email of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => 'jean.doe@free.fr',
        ]
    )]
    #[ApiFilter(SearchFilter::class, strategy: 'istart')]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read'])]
    protected ?string $email = null;

    #[ApiProperty(
        description: 'The Finess number of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => '740787791',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $finessNumber = null;

    #[ApiProperty(
        description: 'The CPS number of the doctor',
        required: false,
        openapiContext: [
            'type' => 'string',
            'example' => '2800089831',
        ]
    )]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $cpsNumber = null;

    #[ApiProperty(readable: false, writable: false)]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $fullName = null;

    #[ApiProperty(readable: false, writable: false)]
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $fullNameInversed = null;

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function setCanonical(?string $canonical): void
    {
        $this->canonical = $canonical;
    }

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

        $this->concatFullNames();

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

        $this->concatFullNames();

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
    #[SerializedName('fullName')]
    public function getFullNameWithTitle(): string
    {
        return trim((string) "{$this->shortTitle()} {$this->getFirstName()} {$this->getLastName()}");
    }

    public function getFullNameInversed(): ?string
    {
        return $this->fullNameInversed;
    }

    public function setFullNameInversed(?string $fullNameInversed): void
    {
        $this->fullNameInversed = $fullNameInversed;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    private function concatFullNames(): void
    {
        $this->fullName = "{$this->getFirstName()} {$this->getLastName()}";
        $this->fullNameInversed = "{$this->getLastName()} {$this->getFirstName()}";
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

    public function getCityEntity(): ?City
    {
        return $this->cityEntity;
    }

    public function setCityEntity(?City $cityEntity): self
    {
        $this->cityEntity = $cityEntity;

        return $this;
    }

    public function getCity(): ?string
    {
        if ($this->cityEntity) {
            return $this->cityEntity->getName();
        }

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

    public function getSpecialty(): ?string
    {
        if ($this->specialtyEntity) {
            return $this->specialtyEntity->getName();
        }

        return $this->specialty;
    }

    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    public function getSpecialtyEntity(): ?Specialty
    {
        return $this->specialtyEntity;
    }

    public function setSpecialtyEntity(?Specialty $specialtyEntity): void
    {
        $this->specialtyEntity = $specialtyEntity;
    }

    public function getAddressExtension(): ?string
    {
        return $this->addressExtension;
    }

    public function setAddressExtension(?string $addressExtension): void
    {
        $this->addressExtension = $addressExtension;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
