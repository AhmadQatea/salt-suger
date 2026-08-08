<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize a Syrian mobile number to +9639XXXXXXXX.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00963')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '963') && strlen($digits) === 12) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '09') && strlen($digits) === 10) {
            return '+963'.substr($digits, 1);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 9) {
            return '+963'.$digits;
        }

        return null;
    }

    /**
     * Determine whether the phone looks like a valid Syrian mobile number.
     */
    public static function isValid(?string $phone): bool
    {
        $normalized = self::normalize($phone);

        return is_string($normalized) && (bool) preg_match('/^\+9639\d{8}$/', $normalized);
    }
}
