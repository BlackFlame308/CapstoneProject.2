<?php

namespace App\Services\Csv;

use DateTime;
use Carbon\Carbon;

/**
 * CsvDateParser
 *
 * Parses various birth date string formats into a Y-m-d string.
 * Supports: MM/DD/YYYY, YYYY-MM-DD, DD/MM/YYYY, and Carbon fallback.
 */
class CsvDateParser
{
    public static function parse(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        // MM/DD/YYYY
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $raw, $m)) {
            if (checkdate((int) $m[1], (int) $m[2], (int) $m[3]) && (int) $m[3] >= 1900) {
                return DateTime::createFromFormat('m/d/Y', $raw)?->format('Y-m-d');
            }
        }

        // YYYY-MM-DD
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $raw)) {
            $date = DateTime::createFromFormat('Y-m-d', $raw);
            return ($date && $date->format('Y-m-d') === $raw) ? $raw : null;
        }

        // DD/MM/YYYY fallback
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $raw, $m)) {
            if (checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
