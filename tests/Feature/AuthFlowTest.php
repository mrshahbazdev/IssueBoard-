<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_land_on_the_login_screen(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }

    public function test_user_can_sign_in_and_out(): void
    {
        $user = User::factory()->create([
            'email' => 'member@issueboard.test',
            'password' => 'Secret123',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Secret123',
        ])->assertRedirect(route('issueboard.index'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_registration_creates_a_team_member(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Reporter',
            'email' => 'new@issueboard.test',
            'phone' => '+49 555 1000',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertRedirect(route('issueboard.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'new@issueboard.test',
            'role' => UserRole::Member->value,
            'phone' => '+49 555 1000',
        ]);
    }

    public function test_profile_and_password_can_be_updated(): void
    {
        $user = User::factory()->create(['password' => 'Secret123']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@issueboard.test',
                'phone' => '+49 555 2000',
            ])
            ->assertSessionHas('status', 'Profile updated.');

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'Secret123',
                'password' => 'Better123',
                'password_confirmation' => 'Better123',
            ])
            ->assertSessionHas('status', 'Password updated.');

        $this->assertTrue(Hash::check('Better123', $user->refresh()->password));
    }
}
