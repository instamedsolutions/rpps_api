<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'translation')]
#[ORM\Entity]
class Translation extends BaseEntity
{
    #[ORM\Column(name: 'lang', type: 'string', length: 5, nullable: false)]
    private ?string $lang = null;

    #[ORM\Column(name: 'field', type: 'string', length: 64, nullable: false)]
    private ?string $field = null;

    #[ORM\Column(name: 'translation', type: 'text', nullable: false)]
    private ?string $translation = null;

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): void
    {
        $this->lang = $lang;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): void
    {
        $this->field = $field;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(?string $translation): void
    {
        $this->translation = $translation;
    }

    public function __toString(): string
    {
        return '' . $this->translation;
    }
}
