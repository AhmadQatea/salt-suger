<?php

namespace App\Support;

class Money
{
    /**
     * Format a decimal money amount with thousands separators and currency.
     */
    public static function format(string|int|float $amount, string $currency): string
    {
        $normalized = bcadd((string) $amount, '0', 2);

        if (str_ends_with($normalized, '.00')) {
            $integer = substr($normalized, 0, -3);
            $formatted = self::thousands($integer);
        } else {
            [$integer, $decimals] = explode('.', $normalized, 2);
            $formatted = self::thousands($integer).'.'.$decimals;
        }

        return $formatted.' '.$currency;
    }

    /**
     * Format amount digits only (no currency), for modal totals.
     */
    public static function digits(string|int|float $amount): string
    {
        $normalized = bcadd((string) $amount, '0', 2);

        if (str_ends_with($normalized, '.00')) {
            return self::thousands(substr($normalized, 0, -3));
        }

        [$integer, $decimals] = explode('.', $normalized, 2);

        return self::thousands($integer).'.'.$decimals;
    }

    /**
     * Multiply a decimal amount by an integer quantity.
     */
    public static function multiply(string|int|float $amount, int $quantity): string
    {
        return bcmul(bcadd((string) $amount, '0', 2), (string) $quantity, 2);
    }

    /**
     * Add two decimal amounts.
     */
    public static function add(string|int|float $left, string|int|float $right): string
    {
        return bcadd((string) $left, (string) $right, 2);
    }

    protected static function thousands(string $integer): string
    {
        $negative = str_starts_with($integer, '-');
        $digits = ltrim($integer, '+-');

        if ($digits === '') {
            $digits = '0';
        }

        $formatted = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $digits) ?? $digits;

        return $negative ? '-'.$formatted : $formatted;
    }
}
