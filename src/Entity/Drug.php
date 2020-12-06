<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\DrugRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
 */
class Drug
{
    /**
     *
     * @var string
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid",unique=true)
     */
    protected $id;

    /**
     *
     * @var string|null
     *
     * The unique CIS Id in the government database
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
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
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $pharmaceuticalForm;

    /**
     *
     * @var array
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $administrationForms;

    /**
     * @var string|null
     *
     * The pharmaceutical company owning the drug
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $owner;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $presentationLabel;

    /**
     * @var array|null
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $reimbursementRates;

    /**
     *
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    protected $price;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $prescriptionConditions;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $genericType;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $genericGroupId;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $genericLabel;


    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
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
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getPharmaceuticalForm(): ?string
    {
        return $this->pharmaceuticalForm;
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
        return $this->owner;
    }

    /**
     * @param string|null $owner
     */
    public function setOwner(?string $owner): void
    {
        $this->owner = $owner;
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
    public function getGenericGroupId(): ?string
    {
        return $this->genericGroupId;
    }

    /**
     * @param string|null $genericGroupId
     */
    public function setGenericGroupId(?string $genericGroupId): void
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




}
