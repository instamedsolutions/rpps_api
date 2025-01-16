<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface TranslatableEntityInterface
{
    public function getId(): string|int|null;

    public function getTranslations(): Collection;

    public function setTranslations(Collection $translations): void;

    public function addTranslation(Translation $translation): void;

    public function setTranslation(string $lang, string $field, string $value): void;

    public function getTranslationsForLang(string $lang): array;

    public function getTranslationsForLangs(array $langs): array;

    public function getAllTranslationsForField(string $field): array;

    public function getIgnoredTranslations(): array;

    /**
     * Get the default language for the entity.
     */
    public function getDefaultLanguage(): string;
}
