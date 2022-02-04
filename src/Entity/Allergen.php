<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\AllergenFilter;
use App\Repository\AllergenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass=AllergenRepository::class)
 *
 * @ORM\Table(name="allergens",indexes={
 *     @ORM\Index(name="allergens_index", columns={"allergen_code"})
 * })
 *
 * @UniqueEntity("code")
 *
 * Liste extracted from
 * https://biologiepathologie.chu-lille.fr/fichiers/42_795catalogue-rast-i.pdf
 *
 * @ApiFilter(AllergenFilter::class,properties={"search"})
 *
 */
class Allergen extends Thing implements Entity
{



    /**
     *
     * @var string|null
     *
     * The unique code of the disease
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
     *              "example"="g206"
     *         }
     *     }
     * )
     *
     * @ORM\Column(name="allergen_code",type="string",length=10,unique=true)
     */
    protected $code;

    /**
     *
     * @var string|null
     *
     * The name of the drug
     *
     * @Groups({"read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="istart")
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Maïs"
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
     * @Groups({"read"})
     *
     * @ApiProperty(
     *     required=true,
     *     attributes={
     *         "openapi_context"={
     *             "type"="string",
     *              "example"="Pollens de graminées"
     *         }
     *     }
     * )
     *
     * @ORM\Column(name="allergen_group",type="string", length=255)
     */
    protected $group;

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
        $this->code = trim($code);
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
        $this->name = trim($name);
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     */
    public function setGroup(?string $group): void
    {
        $this->group = trim($group);
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getName();
    }


}
