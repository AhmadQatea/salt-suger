<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile(): void
    {
        $this->get(route('admin.profile'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_update_email(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'email' => 'new-admin@gmail.com',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'new-admin@gmail.com',
        ]);
    }

    public function test_admin_cannot_use_invalid_or_duplicate_email(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
        ]);

        User::factory()->create([
            'email' => 'taken@gmail.com',
        ]);

        $this->actingAs($admin)->from(route('admin.profile'))->put(route('admin.profile.update'), [
            'email' => 'not-an-email',
        ])->assertRedirect(route('admin.profile'))
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)->from(route('admin.profile'))->put(route('admin.profile.update'), [
            'email' => 'taken@gmail.com',
        ])->assertRedirect(route('admin.profile'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_password_with_correct_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'email' => 'admin@gmail.com',
            'current_password' => 'admin123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHas('status');

        $admin->refresh();

        $this->assertTrue(Hash::check('newpassword123', $admin->password));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_password_update_fails_with_wrong_current_password(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->actingAs($admin)->from(route('admin.profile'))->put(route('admin.profile.update'), [
            'email' => 'admin@gmail.com',
            'current_password' => 'wrong-password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.profile'));
        $response->assertSessionHasErrors('current_password');

        $admin->refresh();

        $this->assertTrue(Hash::check('admin123', $admin->password));
    }
}
