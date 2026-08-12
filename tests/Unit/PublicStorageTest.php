<?php

namespace Tests\Unit;

use App\Support\PublicStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_uploads_resolve_to_media_route(): void
    {
        Config::set('filesystems.disks.public.driver', 'local');
        Storage::fake('public');

        Storage::disk('public')->put('products/test.jpg', 'image-bytes');

        $url = PublicStorage::url('products/test.jpg', 123);

        $this->assertStringContainsString('/media/products/test.jpg', $url);
        $this->assertStringContainsString('v=123', $url);
    }

    public function test_object_storage_urls_use_configured_public_base(): void
    {
        Config::set('filesystems.disks.public', [
            'driver' => 's3',
            'key' => 'test',
            'secret' => 'test',
            'region' => 'auto',
            'bucket' => 'salt-suger',
            'url' => 'https://cdn.example.test/uploads',
            'endpoint' => 'https://example.r2.cloudflarestorage.com',
            'use_path_style_endpoint' => false,
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ]);

        $url = PublicStorage::url('products/demo.webp');

        $this->assertSame('https://cdn.example.test/uploads/products/demo.webp', $url);
    }

    public function test_image_service_stores_with_public_visibility(): void
    {
        Storage::fake('public');

        $service = app(\App\Services\ImageService::class);
        $path = $service->store(UploadedFile::fake()->create('burger.jpg', 100, 'image/jpeg'), 'products');

        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('products/', $path);
    }
}
