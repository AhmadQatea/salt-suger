<?php

namespace App\Services;

use App\Support\PublicStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Store an uploaded image under the given directory on the public disk.
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;

        $path = $file->storeAs($directory, $filename, [
            'disk' => PublicStorage::DISK,
            'visibility' => 'public',
        ]);

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Failed to store uploaded image.');
        }

        return $path;
    }

    /**
     * Delete an image from the public disk if it exists.
     */
    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        if (PublicStorage::disk()->exists($path)) {
            PublicStorage::disk()->delete($path);
        }
    }

    /**
     * Store a new image, then remove the previous one if present.
     */
    public function replace(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $newPath = $this->store($file, $directory);

        if ($oldPath && $oldPath !== $newPath) {
            $this->delete($oldPath);
        }

        return $newPath;
    }
}
