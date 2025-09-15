<?php

namespace App\Entity;

use App\Doctrine\Types\PointType;
use App\Entity\Traits\ImportIdTrait;
use App\Repository\RPPSAddressRepository;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Table(name: 'rpps_address')]
#[ORM\Index(columns: ['rpps_id'], name: 'idx_rppsaddress_rpps')]
#[ORM\Index(columns: ['md5_address'], name: 'idx_rppsaddress_md5')]
#[ORM\UniqueConstraint(name: 'uniq_rppsaddress_rpps_md5', columns: ['rpps_id', 'md5_address'])]
#[ORM\Entity(repositoryClass: RPPSAddressRepository::class)]
class RPPSAddress extends BaseEntity implements ImportableEntityInterface
{
    use ImportIdTrait;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RPPS $rpps = null;

    /**
     * MD5(address|city|zipcode) normalisé. Stocké en hex (32 chars).
     */
    #[ORM\Column(name: 'md5_address', type: 'string', length: 32, nullable: false)]
    private string $md5Address;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $address = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $addressExtension = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $zipcode = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $originalAddress = null;

    #[Groups(['read'])]
    #[ORM\ManyToOne(targetEntity: City::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    private ?City $city = null;

    /**
     * Coordonnées de l’adresse (cabinet), pas celles de la ville.
     */
    #[Groups(['read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $latitude = null;

    #[Groups(['read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $longitude = null;

    #[Groups(['read'])]
    #[ORM\Column(type: PointType::POINT, nullable: false)]
    private array $coordinates = [];

    public function getRpps(): ?RPPS
    {
        return $this->rpps;
    }

    public function setRpps(?RPPS $rpps): static
    {
        $this->rpps = $rpps;

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
        if (null === $address) {
            $this->address = null;

            return $this;
        }

        // Normalize spaces and trim
        $normalized = trim(preg_replace('#\s+#', ' ', $address));

        // Consider an empty string or literal "0" as null
        $this->address = ('' === $normalized || '0' === $normalized) ? null : $normalized;

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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    #[Groups(['read'])]
    #[SerializedName('cityName')]
    public function getCityName(): ?string
    {
        if ($this->city) {
            return $this->city->getName();
        }

        // Fallback to legacy code, remove when all addresses are updated and city field dropped.
        if (!$this->getRpps()?->getCity()) {
            return null;
        }

        return trim(preg_replace('#^\\d{5,6}#', '', $this->getRpps()->getCity()));
    }

    public function getAddressExtension(): ?string
    {
        return $this->addressExtension;
    }

    public function setAddressExtension(?string $addressExtension): void
    {
        $this->addressExtension = $addressExtension;
    }

    public function getOriginalAddress(): ?string
    {
        return $this->originalAddress;
    }

    public function setOriginalAddress(?string $originalAddress): void
    {
        $this->originalAddress = $originalAddress;
    }

    /**
     * Helper to recompute and set the originalAddress from current entity fields.
     *
     * Warning: call this AFTER setting address, addressExtension, zipcode and city,
     * so the computed value is consistent across the application (imports, fixtures, etc.).
     */
    public function refreshOriginalAddress(): void
    {
        $original = trim(implode(' ', array_filter([
            $this->getAddress(),
            $this->getAddressExtension(),
            $this->getZipcode(),
            $this->getCity(),
        ], static fn ($v) => null !== $v && '' !== $v)));

        $this->setOriginalAddress($original ?: null);
    }

    public function __toString(): string
    {
        $parts = array_filter([
            $this->getAddress(),
            $this->getZipcode(),
            $this->getCity(),
        ]);

        return implode(' ', $parts) ?: $this->getId() ?? '';
    }

    /**
     * Setter direct pour le MD5 hexadécimal (32 chars).
     */
    public function setMd5AddressHex(string $hex32): void
    {
        if (32 !== strlen($hex32)) {
            throw new InvalidArgumentException('md5Address must be 32 chars hex.');
        }
        $this->md5Address = strtolower($hex32);
    }

    /**
     * Getter du MD5 au format hexadécimal (32 chars).
     */
    public function getMd5AddressHex(): string
    {
        return $this->md5Address ?? '';
    }

    /**
     * Calcule et affecte le MD5 (hex) à partir des champs normalisés.
     */
    public function setMd5AddressFromParts(?string $address, ?string $city, ?string $zipcode): void
    {
        $normAddr = self::normalizeText($address);
        $normCity = self::normalizeText($city);
        $normZip = self::normalizeText($zipcode);

        $toHash = $normAddr . '|' . $normCity . '|' . $normZip;

        $this->md5Address = md5($toHash);
    }

    private static function normalizeText(?string $value): string
    {
        if (null === $value) {
            return '';
        }
        // trim and collapse whitespaces + lowercase ASCII
        $v = trim(preg_replace('#\s+#', ' ', $value));
        if ('' === $v || '0' === $v) {
            return '';
        }
        // passage en ASCII basique (rapide), enlever accents si nécessaire selon vos besoins
        $v = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $v) ?: $v;

        return strtolower($v);
    }

    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    /**
     * Helper to sync the "coordinates" point field from the current latitude/longitude properties.
     * Use this after you've set latitude and/or longitude to ensure the DB point field is consistent.
     */
    public function syncCoordinatesFromLatLong(): void
    {
        $this->coordinates = [
            'latitude' => $this->latitude ?? 0.0,
            'longitude' => $this->longitude ?? 0.0,
        ];
    }

    public function getLatitude(): ?float
    {
        if (isset($this->coordinates['latitude']) && 0 !== $this->coordinates['latitude']) {
            return $this->coordinates['latitude'];
        }

        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
        $this->coordinates = [
            'latitude' => $latitude ?? 0.0,
            'longitude' => $this->longitude ?? 0.0,
        ];
    }

    public function getLongitude(): ?float
    {
        if (isset($this->coordinates['longitude']) && 0 !== $this->coordinates['longitude']) {
            return $this->coordinates['longitude'];
        }

        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
        $this->coordinates = [
            'latitude' => $this->latitude ?? 0.0,
            'longitude' => $longitude ?? 0.0,
        ];
    }
}
