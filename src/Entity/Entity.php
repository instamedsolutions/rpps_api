<?php

namespace App\Entity;

use DateTimeInterface;

interface Entity
{
    public function getId(): string;

    public function getCreatedDate(): ?DateTimeInterface;

    public function setCreatedDate(?DateTimeInterface $createdDate): void;

    public function __toString(): string;
}
