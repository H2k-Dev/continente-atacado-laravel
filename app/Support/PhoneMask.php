<?php

namespace App\Support;

class PhoneMask
{
    public static function digits(?string $value): string
    {
        return preg_replace('/\D/', '', $value ?? '') ?? '';
    }

    public static function format(?string $value): string
    {
        $digits = self::digits($value);

        if ($digits === '') {
            return '';
        }

        $ddd = substr($digits, 0, 2);
        $rest = substr($digits, 2);

        if (strlen($digits) <= 2) {
            return "($ddd";
        }

        if (strlen($digits) <= 6) {
            return "($ddd) $rest";
        }

        if (strlen($digits) <= 10) {
            return sprintf('(%s) %s-%s', $ddd, substr($rest, 0, 4), substr($rest, 4));
        }

        return sprintf('(%s) %s-%s', $ddd, substr($rest, 0, 5), substr($rest, 5));
    }

    public static function isValid(?string $value): bool
    {
        $length = strlen(self::digits($value));

        return $length >= 10 && $length <= 11;
    }
}