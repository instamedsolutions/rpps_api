<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 */
abstract class Thing implements Entity
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
     * @return string
     */
    abstract public function __toString() : string;

}
