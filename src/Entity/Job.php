<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\ApiPlatform\Filter\JobFilter;
use App\Repository\JobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\Entity(repositoryClass=JobRepository::class)
 *
 * @ORM\Table(name="jobs",indexes={
 *     @ORM\Index(name="jobs_index", columns={"code"})
 * })
 *
 * @UniqueEntity("code")
 *
 * @ApiFilter(JobFilter::class,properties={"search"})
 *
 */
class Job extends Thing implements Entity
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
     *              "example"="J-01220"
     *         }
     *     }
     * )
     *
     * @ORM\Column(name="code",type="string",length=10,unique=true)
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
     *              "example"="physicien en mécanique"
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
     *              "example"="*"
     *         }
     *     }
     * )
     *
     * @ORM\Column(name="mode",type="string", length=5, nullable=true)
     */
    protected $mode;

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
     *              "example"="01"
     *         }
     *     }
     * )
     *
     * @ORM\Column(name="class",type="string", length=5, nullable=true)
     */
    protected $class;


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
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * @param string|null $mode
     */
    public function setMode(?string $mode): void
    {
        $this->mode = trim($mode);
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string|null $class
     */
    public function setClass(?string $class): void
    {
        $this->class = $class;
    }


    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->getName();
    }


}
