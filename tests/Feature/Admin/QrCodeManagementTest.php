<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class QrCodeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_access_qr_page(): void
    {
        $this->get(route('admin.qr-code.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_download_qr(): void
    {
        $this->get(route('admin.qr-code.download', ['format' => 'svg']))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_cannot_access_qr_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.qr-code.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_qr_page(): void
    {
        $menuUrl = route('menu.display', absolute: true);

        $this->actingAs($this->admin())
            ->get(route('admin.qr-code.index'))
            ->assertOk()
            ->assertSee('QR كود المينيو', false)
            ->assertSee('نسخ رابط المينيو', false)
            ->assertSee('تحميل SVG', false)
            ->assertSee('طباعة QR', false)
            ->assertSee('امسح الكود لعرض المينيو', false)
            ->assertSee($menuUrl, false)
            ->assertSee('no-print', false)
            ->assertSee('print-only', false)
            ->assertDontSee('/admin/login', false);
    }

    public function test_qr_menu_url_is_absolute_public_menu_route(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl('https://menu.example.test');
        \Illuminate\Support\Facades\URL::forceScheme('https');

        $service = app(QrCodeService::class);
        $url = $service->menuUrl();

        $this->assertSame('https://menu.example.test/menu/display', $url);
        $this->assertStringStartsWith('https://', $url);
        $this->assertStringContainsString('/menu', $url);
        $this->assertStringNotContainsString('/admin', $url);
        $this->assertStringNotContainsString('localhost', File::get(app_path('Services/QrCodeService.php')));
        $this->assertStringNotContainsString('127.0.0.1', File::get(app_path('Services/QrCodeService.php')));
    }

    public function test_qr_service_generates_non_empty_svg_for_menu_url(): void
    {
        config(['app.url' => 'https://menu.example.test']);

        $service = app(QrCodeService::class);
        $menuUrl = $service->menuUrl();
        $svg = $service->generate(QrCodeService::FORMAT_SVG, $menuUrl);

        $this->assertNotSame('', $svg);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertTrue($service->isSupportedFormat('svg'));
    }

    public function test_unsupported_format_is_rejected_safely(): void
    {
        $service = app(QrCodeService::class);

        $this->assertFalse($service->isSupportedFormat('gif'));

        $this->expectException(\InvalidArgumentException::class);
        $service->generate('gif');
    }

    public function test_admin_can_download_svg_qr(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.qr-code.download', ['format' => 'svg']));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/svg+xml');
        $this->assertNotSame('', $response->getContent());
        $this->assertStringContainsString('<svg', $response->getContent());
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    }

    public function test_png_download_requires_gd_support(): void
    {
        $service = app(QrCodeService::class);

        if ($service->supportsPng()) {
            $response = $this->actingAs($this->admin())
                ->get(route('admin.qr-code.download', ['format' => 'png']));

            $response->assertOk();
            $response->assertHeader('content-type', 'image/png');
            $this->assertNotSame('', $response->getContent());

            return;
        }

        $this->actingAs($this->admin())
            ->get(route('admin.qr-code.download', ['format' => 'png']))
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->get(route('admin.qr-code.index'))
            ->assertOk()
            ->assertDontSee('تحميل PNG', false);
    }

    public function test_qr_navigation_is_highlighted_as_active(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.qr-code.index'))
            ->assertOk()
            ->assertSee('is-active">QR كود المينيو', false);
    }
}
