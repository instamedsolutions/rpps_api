<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Repository\CCAMRepository;
use App\ApiPlatform\Filter\CCAMFilter;

/**
 *
 * @ORM\Entity(repositoryClass=CCAMRepository::class)
 *
 *
 * Definitions :
 * http://www.cpam21.fr/Flashs/flashs/Medecins/Docs/SC506_fichesCCAM.pdf
 *
 * @ORM\Table(name="ccam",indexes={
 *     @ORM\Index(name="ccam_index", columns={"code"})
 * })
 *
 * @UniqueEntity("code")
 *
 * @ApiFilter(CCAMFilter::class,properties={"search"})
 *
 * @ApiFilter(SearchFilter::class, properties={"category.code","group.code"})
 *
 */
class CCAM extends Thing implements Entity
{

    /**
     *
     * @var string|null
     *
     * The unique code in the government database
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @Groups({"read"})
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
     * @ORM\Column(type="string",unique=true)
     */
    protected $code;

    /**
     *
     * @var string|null
     *
     * The name of the drug
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Électromyographie par électrode de surface, avec enregistrement vidéo"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     *
     * @var string|null
     *
     * The name of the drug
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @Groups({"ccams:item:read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Électromyographie par électrode de surface, avec enregistrement vidéo"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="text",nullable=true)
     */
    protected $description;





    /**
     *
     * @var float|null
     *
     * The price rate for the secteur 1
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="number",
     *              "example"=33,56
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="float",nullable=true)
     */
    protected $rate1;

    /**
     *
     * @var float|null
     *
     * The price rate for the secteur 2
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="number",
     *              "example"=33,56
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="float",nullable=true)
     */
    protected $rate2;


    /**
     * @var CCAMGroup|null
     *
     * @Groups({"ccams:read"})
     *
     * @MaxDepth(1)
     *
     * The subgroup the disease is a part of.
     * A group is itself linked to a category
     *
     * @ORM\ManyToOne(targetEntity="CCAMGroup", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     */
    protected $group;

    /**
     * @var CCAMGroup|null
     *
     * The main category the disease is a part of.
     *
     * @MaxDepth(1)
     *
     * @Groups({"ccams:item:read"})
     *
     * @ORM\ManyToOne(targetEntity="CCAMGroup", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     */
    protected $category;

    /**
     *
     * @Groups({"ccams:read"})
     *
     * @ORM\Column(type="json")
     *
     * @var array
     */
    protected $modifiers = [];


    /**
     *
     * @var string|null
     *
     * The unique regroupement code in the government database
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     *
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="ADE"
     *         }
     *     }
     * )
     *
     * @ORM\Column(type="string",length=4)
     */
    protected $regroupementCode;



    public function __construct()
    {
        parent::__construct();

    }


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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }


    /**
     * @param string $description
     */
    public function addDescriptionLine(string $description)
    {
        if(trim($description)) {
            $this->description .= trim(",$description");
        }
    }

    /**
     * @return float|null
     */
    public function getRate1(): ?float
    {
        return $this->rate1;
    }

    /**
     * @param float|null $rate1
     */
    public function setRate1(?float $rate1): void
    {
        $this->rate1 = $rate1;
    }

    /**
     * @return float|null
     */
    public function getRate2(): ?float
    {
        return $this->rate2;
    }

    /**
     * @param float|null $rate2
     */
    public function setRate2(?float $rate2): void
    {
        $this->rate2 = $rate2;
    }

    /**
     * @return CCAMGroup|null
     */
    public function getGroup(): ?CCAMGroup
    {
        return $this->group;
    }

    /**
     * @param CCAMGroup|null $group
     */
    public function setGroup(?CCAMGroup $group): void
    {
        $this->group = $group;
    }

    /**
     * @return CCAMGroup|null
     */
    public function getCategory(): ?CCAMGroup
    {
        return $this->category;
    }

    /**
     * @param CCAMGroup|null $category
     */
    public function setCategory(?CCAMGroup $category): void
    {
        $this->category = $category;
    }

    /**
     * @return array
     */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    /**
     * @param array $modifiers
     */
    public function setModifiers(array $modifiers): void
    {
        $this->modifiers = $modifiers;
    }

    /**
     * @return string|null
     */
    public function getRegroupementCode(): ?string
    {
        return $this->regroupementCode;
    }

    /**
     * @param string|null $regroupementCode
     */
    public function setRegroupementCode(?string $regroupementCode): void
    {
        $this->regroupementCode = $regroupementCode;
    }




    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getName();
    }


}
