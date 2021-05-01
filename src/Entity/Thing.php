<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 */
abstract class Thing implements Entity
{


    /**
     *
     * @var string
     *
     * @Groups({"read"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid",unique=true)
     */
    protected $id;


    /**
     * @var DateTime|null
     *
     * The created date of the entity
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    protected $createdDate;


    /**
     * Thing constructor.
     */
    public function __construct()
    {
        $this->createdDate = new DateTime();
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedDate(): ?DateTime
    {
        return $this->createdDate;
    }

    /**
     * @param DateTime|null $createdDate
     */
    public function setCreatedDate(?DateTime $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    /**
     *
     * Function replacing Maj to dashed
     *
     * https://stackoverflow.com/questions/1993721/how-to-convert-pascalcase-to-pascal-case
     *
     * @param $word
     * @return string
     */
    public static function decamelize(string $word) : string
    {

        $word = lcfirst($word);

        return strtolower(preg_replace(
            '/(^|[a-z])([A-Z])/',
            strtolower(strlen("\\1") ? "\\1_\\2" : "\\2"),
            $word
        ));
    }


    /**
     * @return string
     */
    abstract public function __toString() : string;

}
