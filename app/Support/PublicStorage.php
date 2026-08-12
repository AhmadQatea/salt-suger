<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicStorage
{
    /**
     * Resolve a public URL for a stored file path with optional cache-busting.
     */
    public static function url(?string $path, int|string|null $version = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (self::usesLocalDriver() && ! $disk->exists($path)) {
            return null;
        }

        $url = $disk->url($path);

        if ($version === null || $version === '') {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'v='.rawurlencode((string) $version);
    }

    public static function usesLocalDriver(): bool
    {
        return config('filesystems.disks.public.driver', 'local') === 'local';
    }
}
