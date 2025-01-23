<?php

namespace App\Entity\Traits;

use App\Entity\Translation;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait TranslatableTrait
{
    #[ORM\ManyToMany(targetEntity: Translation::class, cascade: ['persist'])]
    protected Collection $translations;

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setTranslations(Collection $translations): void
    {
        $this->translations = $translations;
    }

    public function addTranslation(Translation $translation): void
    {
        $this->translations->add($translation);
    }

    public function setTranslation(string $lang, string $field, string $value): void
    {
        $lang = str_replace('-', '_', $lang);

        $translation = $this->translations->filter(function (Translation $translation) use ($lang, $field) {
            return $translation->getLang() === $lang && $translation->getField() === $field;
        })->first();

        if ($translation) {
            $translation->setTranslation($value);
        } else {
            $translation = new Translation();
            $translation->setLang($lang);
            $translation->setField($field);
            $translation->setTranslation($value);
            $this->addTranslation($translation);
        }
    }

    public function getTranslationsForLangs(array $langs): array
    {
        foreach ($langs as $lang) {
            if ('*' === $lang) {
                return [];
            }
            $translations = $this->getTranslationsForLang($lang);
            if (!empty($translations)) {
                return $translations;
            }
        }

        return [];
    }

    public function getIgnoredTranslations(): array
    {
        return [];
    }

    public function getTranslationsForLang(string $lang): array
    {
        $translations = $this->translations->filter(function (Translation $translation) use ($lang) {
            return $translation->getLang() === $lang;
        });

        $result = [];
        foreach ($translations as $translation) {
            $result[$translation->getField()] = $translation->getTranslation();
        }

        // This handles en_US -> en
        $mainLang = explode('_', $lang)[0];
        if (empty($result) && $mainLang !== $lang) {
            return $this->getTranslationsForLang($mainLang);
        }

        return $result;
    }

    public function getTranslationForLang(string $lang, string $field): string
    {
        $translations = $this->getTranslationsForLang($lang);

        return $translations[$field] ?? '';
    }

    public function getAllTranslationsForField(string $field): array
    {
        /** @var Translation[] $translations */
        $translations = $this->translations->filter(function (Translation $translation) use ($field) {
            return $translation->getField() === $field;
        });

        $result = [];
        foreach ($translations as $translation) {
            $result[$translation->getLang()] = $translation->getTranslation();
        }

        return $result;
    }

    public function getDefaultLanguage(): string
    {
        return 'fr';
    }
}
