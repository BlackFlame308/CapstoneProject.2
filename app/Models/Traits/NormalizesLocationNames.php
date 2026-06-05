<?php

namespace App\Models\Traits;

trait NormalizesLocationNames
{
    /**
     * Standardize capitalization and spacing of location names.
     */
    public static function normalizeLocationName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        // Lowercase everything first
        $lower = strtolower($name);
        // Capitalize every word starting with a letter, even if preceded by non-word chars like ( or -
        $normalized = preg_replace_callback('/\b[a-z]/', function($matches) {
            return strtoupper($matches[0]);
        }, $lower);
        // Restore roman numerals (I to XVII) and known abbreviations (NCR) to uppercase
        $normalized = preg_replace_callback('/\b(xvii|xvi|xv|xiv|xiii|xii|xi|x|ix|viii|vii|vi|iv|v|iii|ii|i|ncr)\b/i', function($matches) {
            return strtoupper($matches[0]);
        }, $normalized);
        return $normalized;
    }
}
