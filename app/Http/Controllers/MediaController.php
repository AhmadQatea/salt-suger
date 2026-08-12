<?php

namespace App\Http\Controllers;

use App\Support\PublicStorage;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Serve uploaded public files when local storage is used (no symlink required).
     */
    public function show(string $path): StreamedResponse|Response
    {
        if (PublicStorage::usesObjectStorage()) {
            abort(404);
        }

        $path = ltrim(str_replace(['..', '\\'], '', $path), '/');

        if ($path === '' || ! PublicStorage::disk()->exists($path)) {
            abort(404);
        }

        return PublicStorage::disk()->response($path, headers: [
            'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
        ]);
    }
}
