<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visiting_admin_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('تسجيل الدخول إلى لوحة التحكم', false);
    }

    public function test_admin_can_authenticate_with_valid_credentials(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_cannot_authenticate_with_invalid_credentials(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'admin@gmail.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_non_admin_user_cannot_authenticate_to_admin_area(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_admin_can_access_dashboard_and_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('لوحة تحكم Salt&Suger')
            ->assertSee($admin->email);

        $this->actingAs($admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('إعدادات الحساب', false);
    }

    public function test_authenticated_admin_visiting_login_is_redirected_to_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.logout'));

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }
}
