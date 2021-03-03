<?php

namespace App\Entity;

use DateTime;

/**
 *
 */
interface Entity
{

    /**
     * @return string
     */
    public function getId() : string;


    /**
     * @return DateTime|null
     */
    public function getCreatedDate(): ?DateTime;


    /**
     * @param DateTime|null $createdDate
     */
    public function setCreatedDate(?DateTime $createdDate): void;

    /**
     * @return string
     */
    public function __toString() : string;

}
