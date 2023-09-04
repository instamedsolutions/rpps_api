<?php

namespace App\ApiPlatform\Filter;

trait FilterTrait
{
    protected function cleanValue(string $value, bool $replaceSpace = true): string
    {
        $value = trim(preg_replace('#\s+#', ' ', $value));
        if ($replaceSpace) {
            $value = str_replace(' ', '%', $value);
        }

        // https://github.com/symfony/symfony/issues/9326
        $value = transliterator_transliterate('Any-Latin; Latin-ASCII;', $value);

        return $value;
    }
}
