<?php

namespace Tests\Feature\Admin;

use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesFakeImages;
use Tests\TestCase;

class RestaurantSettingsTest extends TestCase
{
    use CreatesFakeImages;
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
        ]);
    }

    protected function seedSettings(array $overrides = []): RestaurantSetting
    {
        return RestaurantSetting::factory()->create(array_merge([
            'restaurant_name' => 'Salt&Suger',
            'description' => 'وصف تجريبي',
            'currency' => 'ل.س',
            'whatsapp_enabled' => false,
            'whatsapp_number' => null,
        ], $overrides));
    }

    public function test_guest_cannot_open_settings(): void
    {
        $this->get(route('admin.settings.edit'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_update_settings(): void
    {
        $this->seedSettings();

        $this->put(route('admin.settings.update'), [
            'restaurant_name' => 'Hacked',
            'currency' => 'ل.س',
            'whatsapp_enabled' => '1',
            'whatsapp_number' => '0912345678',
        ])->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing('restaurant_settings', [
            'restaurant_name' => 'Hacked',
        ]);
    }

    public function test_admin_can_open_settings_and_see_whatsapp_fields(): void
    {
        $this->seedSettings([
            'whatsapp_number' => '+963912345678',
            'whatsapp_enabled' => true,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('إعدادات المطعم', false)
            ->assertSee('رقم واتساب المطعم', false)
            ->assertSee('تفعيل الطلب عبر واتساب', false)
            ->assertSee('+963912345678', false)
            ->assertSee('اسم المطعم', false);
    }

    public function test_admin_can_save_whatsapp_number_normalized(): void
    {
        $this->seedSettings();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'description' => 'وصف محدّث',
                'currency' => 'ل.س',
                'whatsapp_number' => '09 123 456 78',
                'whatsapp_enabled' => '0',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('restaurant_settings', [
            'whatsapp_number' => '+963912345678',
            'whatsapp_enabled' => 0,
            'description' => 'وصف محدّث',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('+963912345678', false);
    }

    public function test_admin_can_enable_whatsapp_ordering(): void
    {
        $this->seedSettings([
            'whatsapp_enabled' => false,
            'whatsapp_number' => '+963911111111',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_number' => '+963911111111',
                'whatsapp_enabled' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertTrue(RestaurantSetting::query()->first()->whatsapp_enabled);
    }

    public function test_admin_can_disable_whatsapp_ordering(): void
    {
        $this->seedSettings([
            'whatsapp_enabled' => true,
            'whatsapp_number' => '+963911111111',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_number' => '+963911111111',
                // unchecked checkbox omitted
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertFalse(RestaurantSetting::query()->first()->whatsapp_enabled);
    }

    public function test_invalid_phone_number_is_rejected(): void
    {
        $this->seedSettings();

        $this->actingAs($this->admin())
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_number' => '12345',
                'whatsapp_enabled' => '0',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors('whatsapp_number');

        $this->assertDatabaseMissing('restaurant_settings', [
            'whatsapp_number' => '12345',
        ]);
    }

    public function test_enabling_whatsapp_without_number_is_rejected(): void
    {
        $this->seedSettings([
            'whatsapp_number' => null,
            'whatsapp_enabled' => false,
        ]);

        $this->actingAs($this->admin())
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_number' => '',
                'whatsapp_enabled' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors('whatsapp_number');

        $this->assertFalse(RestaurantSetting::query()->first()->whatsapp_enabled);
    }

    public function test_settings_nav_link_is_visible_for_admin(): void
    {
        $this->seedSettings();

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('إعدادات المطعم', false)
            ->assertSee(route('admin.settings.edit'), false);
    }

    public function test_admin_can_upload_and_replace_hero_image(): void
    {
        Storage::fake('public');
        $this->seedSettings();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_enabled' => '0',
                'hero_image' => $this->fakeImage('hero.png'),
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $settings = RestaurantSetting::query()->first();
        $this->assertNotNull($settings?->hero_image);
        $this->assertStringStartsWith('restaurant/hero/', $settings->hero_image);
        $this->assertTrue(Storage::disk('public')->exists($settings->hero_image));

        $oldPath = $settings->hero_image;

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_enabled' => '0',
                'hero_image' => $this->fakeImage('hero-new.jpg'),
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $settings->refresh();
        $this->assertNotSame($oldPath, $settings->hero_image);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($settings->hero_image));
    }

    public function test_admin_can_remove_hero_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('restaurant/hero/keep.png', 'fake');

        $this->seedSettings([
            'hero_image' => 'restaurant/hero/keep.png',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.settings.update'), [
                'restaurant_name' => 'Salt&Suger',
                'currency' => 'ل.س',
                'whatsapp_enabled' => '0',
                'remove_hero_image' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertNull(RestaurantSetting::query()->value('hero_image'));
        $this->assertFalse(Storage::disk('public')->exists('restaurant/hero/keep.png'));
    }

    public function test_admin_sees_hero_image_settings_section(): void
    {
        $this->seedSettings();

        $this->actingAs($this->admin())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('صورة الغلاف الرئيسية', false)
            ->assertSee('إعدادات المظهر', false);
    }
}
