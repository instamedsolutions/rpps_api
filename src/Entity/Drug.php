<?php

namespace App\Entity;

use Stringable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\DrugsFilter;
use App\Repository\DrugRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;


#[ApiFilter(DrugsFilter::class, properties: ["search"])]
#[ORM\Entity(repositoryClass: DrugRepository::class)]
#[ORM\Table(name: 'drugs')]
#[ORM\Index(name: 'drugs_index', columns: ['cis_id'])]
#[UniqueEntity('cisId')]
class Drug extends Thing implements Entity, Stringable
{
    final const GENERIC_LABEL_PRINCEPS = 1;
    final const GENERIC_LABEL_GENERIC = 2;
    final const GENERIC_LABEL_GENERIC_BY_COMPLEMENTARITY_POSOLOGIC = 3;
    final const GENERIC_LABEL_GENERIC_SUBSTITUABLE = 3;


    #[ApiFilter(SearchFilter::class, strategy: "exact")]
    #[ApiProperty(description: "The unique CIS Id in the government database", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "66595239"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    protected ?string $cisId = null;


    #[ApiFilter(SearchFilter::class, strategy: "istart")]
    #[ApiProperty(description: "The name of the drug", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "PANTOPRAZOLE KRKA 40 mg, comprimé gastro-résistant"
        ]
    ])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name = null;


    #[ApiProperty(description: "The pharmaceutical form of the drug", required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "comprimé gastro-résistant(e)"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $pharmaceuticalForm = null;


    #[ApiProperty(description: "The administration form of the drug", required: true, attributes: [
        "openapi_context" => [
            "type" => "array",
            "items" => [
                "type" => "string",
                "example" => "comprimé gastro-résistant(e)"
            ]
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'array', nullable: true)]
    protected ?array $administrationForms;


    #[ApiProperty(description: "The pharmaceutical company owning the drug", required: false, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => " BIOGARAN"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $owner = null;

    #[ApiProperty(description: "The packaging of the drug", required: false, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "plaquette(s) thermoformée(s) aluminium de 28 comprimé(s)"
        ]
    ])]
    #[Groups(['read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $presentationLabel = null;


    #[ApiProperty(description: "The percentage reimbursed of the drug", required: false, attributes: [
        "openapi_context" => [
            "type" => "array",
            "items" => [
                "type" => "string",
                "example" => ["65%"]
            ]
        ]
    ])]
    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'array', nullable: true)]
    protected ?array $reimbursementRates;


    #[ApiProperty(description: "The price of the drug", required: false, attributes: [
        "openapi_context" => [
            "type" => "float",
            "example" => 3.90
        ]
    ])]
    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'float', nullable: true)]
    protected ?float $price = null;


    #[ApiProperty(description: "The generic label of the drug", required: false, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "liste II"
        ]
    ])]
    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $prescriptionConditions = null;


    #[ApiProperty(required: true, attributes: [
        "openapi_context" => [
            "type" => "string",
            "example" => "PANTOPRAZOLE SODIQUE SESQUIHYDRATE équivalant à PANTOPRAZOLE 40 mg - EUPANTOL 40 mg, comprimé gastro-résistant - INIPOMP 40 mg, comprimé gastro-résistant - PANTIPP 40 mg, comprimé gastro-résistant."
        ]
    ])]
    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $genericType = null;


    #[ApiProperty(required: true, attributes: [
        "openapi_context" => [
            "type" => "int",
            "example" => "143"
        ]
    ])]
    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $genericGroupId = null;


    #[ApiProperty(required: true, attributes: [
        "openapi_context" => [
            "type" => "int",
            "enum" => [
                Drug::GENERIC_LABEL_GENERIC,
                Drug::GENERIC_LABEL_PRINCEPS,
                Drug::GENERIC_LABEL_GENERIC_BY_COMPLEMENTARITY_POSOLOGIC,
                Drug::GENERIC_LABEL_GENERIC_SUBSTITUABLE
            ],
            "example" => Drug::GENERIC_LABEL_GENERIC
        ]
    ])]
    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'smallint', nullable: true)]
    protected ?string $genericLabel = null;


    #[Groups(['drugs:item:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $securityText = null;


    public function getId(): string
    {
        return $this->id;
    }

    public function getCisId(): ?string
    {
        return $this->cisId;
    }

    public function setCisId(?string $cisId): void
    {
        $this->cisId = $cisId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    #[Groups(['read'])]
    #[SerializedName('name')]
    public function getShortName(): ?string
    {
        return $this->splitName()[0];
    }

    #[Groups(['read'])]
    public function getFormat(): ?string
    {
        return $this->splitName()[1];
    }

    public function setName(?string $name): void
    {
        $this->name = trim($name);
    }

    public function getPharmaceuticalForm(): ?string
    {
        return preg_replace("#\s+#", " ", $this->pharmaceuticalForm);
    }

    public function setPharmaceuticalForm(?string $pharmaceuticalForm): void
    {
        $this->pharmaceuticalForm = $pharmaceuticalForm;
    }

    /**
     * @return array
     */
    public function getAdministrationForms(): ?array
    {
        return $this->administrationForms;
    }

    public function setAdministrationForms(?array $administrationForms = null): void
    {
        $this->administrationForms = $administrationForms;
    }

    public function getOwner(): ?string
    {
        return trim($this->owner);
    }

    public function setOwner(?string $owner): void
    {
        $this->owner = trim($owner);
    }

    public function getPresentationLabel(): ?string
    {
        return $this->presentationLabel;
    }

    public function setPresentationLabel(?string $presentationLabel): void
    {
        $this->presentationLabel = $presentationLabel;
    }

    public function getReimbursementRates(): ?array
    {
        return $this->reimbursementRates;
    }

    public function setReimbursementRates(?array $reimbursementRates): void
    {
        $this->reimbursementRates = $reimbursementRates;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getPrescriptionConditions(): ?string
    {
        return $this->prescriptionConditions;
    }

    public function setPrescriptionConditions(?string $prescriptionConditions): void
    {
        $this->prescriptionConditions = $prescriptionConditions;
    }

    /**
     * @return string|null
     */
    public function getGenericGroupId(): ?int
    {
        return $this->genericGroupId;
    }

    /**
     * @param string|null $genericGroupId
     */
    public function setGenericGroupId(?int $genericGroupId): void
    {
        $this->genericGroupId = $genericGroupId;
    }

    public function getGenericType(): ?string
    {
        return $this->genericType;
    }

    public function setGenericType(?string $genericType): void
    {
        $this->genericType = $genericType;
    }

    public function getGenericLabel(): ?string
    {
        return $this->genericLabel;
    }

    public function setGenericLabel(?string $genericLabel): void
    {
        $this->genericLabel = $genericLabel;
    }

    public function getSecurityText(): ?string
    {
        return $this->securityText;
    }

    public function setSecurityText(?string $securityText): void
    {
        $this->securityText = $securityText;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getShortName();
    }

    protected function splitName()
    {
        $name = $this->getName();

        $separator = ",";
        // Remove last part of the name
        $name = explode($separator, $name);
        if (count($name) === 1) {
            $separator = ".";
            $name = explode($separator, $name[0]);
        }

        $format = trim(array_pop($name));
        $name = implode($separator, $name);

        return [$name, $format];
    }
}
