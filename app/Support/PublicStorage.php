<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicStorage
{
    public const DISK = 'public';

    /**
     * Resolve a public URL for a stored file path with optional cache-busting.
     */
    public static function url(?string $path, int|string|null $version = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = ltrim($path, '/');

        if (self::usesObjectStorage()) {
            $url = self::objectStorageUrl($path);
        } else {
            $url = self::localUrl($path);
        }

        if ($version === null || $version === '') {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'v='.rawurlencode((string) $version);
    }

    public static function usesObjectStorage(): bool
    {
        return config('filesystems.disks.'.self::DISK.'.driver', 'local') === 's3';
    }

    public static function usesLocalDriver(): bool
    {
        return ! self::usesObjectStorage();
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public static function disk()
    {
        return Storage::disk(self::DISK);
    }

    protected static function objectStorageUrl(string $path): string
    {
        $baseUrl = config('filesystems.disks.'.self::DISK.'.url')
            ?: env('AWS_URL');

        if (filled($baseUrl)) {
            return rtrim((string) $baseUrl, '/').'/'.ltrim($path, '/');
        }

        $generated = self::disk()->url($path);

        if (Str::startsWith($generated, ['http://', 'https://'])) {
            return $generated;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($generated, '/');
    }

    protected static function localUrl(string $path): string
    {
        if (Route::has('media.show')) {
            return route('media.show', ['path' => $path], absolute: false);
        }

        $generated = self::disk()->url($path);

        if (Str::startsWith($generated, ['http://', 'https://'])) {
            return $generated;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($generated, '/');
    }
}
