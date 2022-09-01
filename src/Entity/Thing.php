<?php

namespace App\Entity;

use Stringable;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use function Symfony\Component\String\u;

/**
 *
 */
abstract class Thing implements Entity, Stringable
{


    /**
     *
     * @var string
     *
     *
     */
    #[Groups(['read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'UUID')]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $id;


    /**
     * @var DateTime|null
     *
     * The created date of the entity
     */
    #[ORM\Column(name: 'created_date', type: 'datetime')]
    protected $createdDate;


    /**
     * Thing constructor.
     */
    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }


    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }


    public function setCreatedDate(DateTimeInterface|null $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    /**
     *
     * Function replacing Maj to dashed
     *
     * https://stackoverflow.com/questions/1993721/how-to-convert-pascalcase-to-pascal-case
     **/
    public static function decamelize(string $word): string
    {
        return u($word)->snake()->toString();
    }


    /**
     * @return string
     */
    abstract public function __toString(): string;

}
