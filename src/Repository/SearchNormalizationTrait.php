<?php

namespace App\Repository;

trait SearchNormalizationTrait
{
    /**
     * Normalize search term by removing accents, spaces, and hyphens
     */
    private function normalizeSearchTerm(string $search): string
    {
        // Remove accents using transliterator if available
        if (extension_loaded('intl')) {
            $transliterator = \Transliterator::createFromRules(
                ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
                \Transliterator::FORWARD
            );
            if ($transliterator) {
                $search = $transliterator->transliterate($search);
            }
        } else {
            // Fallback to iconv if intl extension is not available
            $search = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $search) ?: $search;
        }
        
        // Remove spaces and hyphens
        $search = str_replace([' ', '-'], '', $search);
        
        return $search;
    }
}
