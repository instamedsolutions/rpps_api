<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\DrugsFilter;
use App\Repository\DrugRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 *
 * @ORM\Entity(repositoryClass=DrugRepository::class)
 *
 * @ORM\Table(name="drugs",indexes={
 *     @ORM\Index(name="drugs_index", columns={"cis_id"})
 * })
 *
 * @UniqueEntity("cisId")
 *
 * @ApiFilter(DrugsFilter::class,properties={"search"})
 *
 */
class Drug extends Thing implements Entity
{

    const GENERIC_LABEL_PRINCEPS = 1;

    const GENERIC_LABEL_GENERIC = 2;

    const GENERIC_LABEL_GENERIC_BY_COMPLEMENTARITY_POSOLOGIC = 3;

    const GENERIC_LABEL_GENERIC_SUBSTITUABLE = 3;


    /**
     *
     * @var string|null
     *
     * The unique CIS Id in the government database
     *
     * @Groups({"read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="66595239"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", nullable=true,unique=true)
     */
    protected $cisId;

    /**
     *
     * @var string|null
     *
     * The name of the drug
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="PANTOPRAZOLE KRKA 40 mg, comprimé gastro-résistant"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, options={"collation":"utf8mb4_unicode_ci"})
     */
    protected $name;

    /**
     *
     * @var string|null
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="comprimé gastro-résistant(e)"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $pharmaceuticalForm;

    /**
     *
     * @var array
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="array",
     *              "items"={
     *                  "type"="string",
     *                  "example"="comprimé gastro-résistant(e)"
     *               }
     *            }
     *     }
     * )
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $administrationForms;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     *
     * The pharmaceutical company owning the drug
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"=" BIOGARAN"
     *         }
     *     }
     * )
     *
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $owner;

    /**
     * @var string|null
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="plaquette(s) thermoformée(s) aluminium de 28 comprimé(s)"
     *         }
     *     }
     * )
     *
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $presentationLabel;

    /**
     * @var array|null
     *
     * @Groups({"drugs:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="array",
     *              "items"={
     *                  "type"="string",
     *                  "example"="65%"
     *               }
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $reimbursementRates;

    /**
     *
     * @var float|null
     *
     *
     * @Groups({"drugs:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="float",
     *              "example"=3,90
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="float", nullable=true)
     */
    protected $price;

    /**
     *
     * @var string|null
     *
     * @Groups({"drugs:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="liste II"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $prescriptionConditions;

    /**
     *
     * @var string|null
     *
     * @Groups({"drugs:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="PANTOPRAZOLE SODIQUE SESQUIHYDRATE équivalant à PANTOPRAZOLE 40 mg - EUPANTOL 40 mg, comprimé gastro-résistant - INIPOMP 40 mg, comprimé gastro-résistant - PANTIPP 40 mg, comprimé gastro-résistant."
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $genericType;

    /**
     *
     * @var int|null
     *
     * @Groups({"drugs:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="int",
     *              "example"="143"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $genericGroupId;

    /**
     *
     * @var string|null
     *
     * @Groups({"drugs:item:read"})
     *
     * @ApiProperty(
     *     required=false,
     *     attributes={
     *         "openapi_context"={
     *             "type"="int",
     *              "enum"={
     *               Drug::GENERIC_LABEL_GENERIC,
     *               Drug::GENERIC_LABEL_PRINCEPS,
     *               Drug::GENERIC_LABEL_GENERIC_BY_COMPLEMENTARITY_POSOLOGIC,
     *               Drug::GENERIC_LABEL_GENERIC_SUBSTITUABLE
     *               },
     *              "example"=Drug::GENERIC_LABEL_GENERIC
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $genericLabel;


    /**
     *
     * @var string|null
     *
     * @Groups({"drugs:item:read"})
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $securityText;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }


    /**
     * @return string|null
     */
    public function getCisId(): ?string
    {
        return $this->cisId;
    }

    /**
     * @param string|null $cisId
     */
    public function setCisId(?string $cisId): void
    {
        $this->cisId = $cisId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        if(null === $this->name) {
            return null;
        }

        return $this->name;
    }

    /**
     *
     * @Groups({"read"})
     *
     * @SerializedName("name")
     *
     * @return string|null
     */
    public function getShortName() : ?string
    {
        return $this->splitName()[0];
    }

    /**
     *
     * @Groups({"read"})
     *
     * @return string|null
     */
    public function getFormat() : ?string
    {
        return $this->splitName()[1];
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = trim($name);
    }

    /**
     * @return string|null
     */
    public function getPharmaceuticalForm(): ?string
    {
        return preg_replace("#\s+#"," ",$this->pharmaceuticalForm);
    }

    /**
     * @param string|null $pharmaceuticalForm
     */
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

    /**
     * @param array|null $administrationForms
     */
    public function setAdministrationForms(?array $administrationForms = null): void
    {
        $this->administrationForms = $administrationForms;
    }

    /**
     * @return string|null
     */
    public function getOwner(): ?string
    {
        return trim($this->owner);
    }

    /**
     * @param string|null $owner
     */
    public function setOwner(?string $owner): void
    {
        $this->owner = trim($owner);
    }

    /**
     * @return string|null
     */
    public function getPresentationLabel(): ?string
    {
        return $this->presentationLabel;
    }

    /**
     * @param string|null $presentationLabel
     */
    public function setPresentationLabel(?string $presentationLabel): void
    {
        $this->presentationLabel = $presentationLabel;
    }

    /**
     * @return array|null
     */
    public function getReimbursementRates(): ?array
    {
        return $this->reimbursementRates;
    }

    /**
     * @param array|null $reimbursementRates
     */
    public function setReimbursementRates(?array $reimbursementRates): void
    {
        $this->reimbursementRates = $reimbursementRates;
    }


    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string|null
     */
    public function getPrescriptionConditions(): ?string
    {
        return $this->prescriptionConditions;
    }

    /**
     * @param string|null $prescriptionConditions
     */
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



    /**
     * @return string|null
     */
    public function getGenericType(): ?string
    {
        return $this->genericType;
    }

    /**
     * @param string|null $genericType
     */
    public function setGenericType(?string $genericType): void
    {
        $this->genericType = $genericType;
    }

    /**
     * @return string|null
     */
    public function getGenericLabel(): ?string
    {
        return $this->genericLabel;
    }

    /**
     * @param string|null $genericLabel
     */
    public function setGenericLabel(?string $genericLabel): void
    {
        $this->genericLabel = $genericLabel;
    }

    /**
     * @return string|null
     */
    public function getSecurityText(): ?string
    {
        return $this->securityText;
    }

    /**
     * @param string|null $securityText
     */
    public function setSecurityText(?string $securityText): void
    {
        $this->securityText = $securityText;
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getShortName();
    }



    protected function splitName()
    {
        $name = $this->getName();

        $separator = ",";
        // Remove last part of the name
        $name = explode($separator,$name);
        if(count($name) === 1) {
            $separator = ".";
            $name = explode($separator,$name[0]);
        }

        $format = trim(array_pop($name));
        $name = implode($separator,$name);

        return [$name,$format];

    }



}
