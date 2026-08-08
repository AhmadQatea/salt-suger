<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;

trait CreatesFakeImages
{
    /**
     * Build a valid tiny PNG upload without requiring the GD extension.
     */
    protected function fakeImage(string $filename = 'photo.png'): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true
        );

        $path = tempnam(sys_get_temp_dir(), 'ssimg_');
        file_put_contents($path, $png);

        return new UploadedFile(
            $path,
            $filename,
            'image/png',
            null,
            true
        );
    }
}
