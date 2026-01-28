<?php

namespace App\Repository;

trait SearchNormalizationTrait
{
    /**
     * Normalize search term by replacing hyphens with spaces
     * 
     * Note: Accent normalization is handled by MySQL's collation (utf8mb4_unicode_ci)
     * which is accent-insensitive by default, so we don't need to remove accents here.
     */
    private function normalizeSearchTerm(string $search): string
    {
        // Replace hyphens with spaces for flexible matching
        return str_replace('-', ' ', $search);
    }
}
