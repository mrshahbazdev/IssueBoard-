<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_can_be_switched_and_persists_in_the_session(): void
    {
        $this->from(route('login'))
            ->post(route('locale.update'), ['locale' => 'de'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('locale', 'de');

        $this->withSession(['locale' => 'de'])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('lang="de"', false)
            ->assertSee('Im Arbeitsbereich anmelden')
            ->assertSee('Neue Aufgabe');
    }

    public function test_issue_board_uses_the_selected_language(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->withSession(['locale' => 'de'])
            ->get(route('issueboard.index'))
            ->assertOk()
            ->assertSee('Interne Projektkommunikation')
            ->assertSee('Team');

        $this->actingAs($user)
            ->withSession(['locale' => 'de'])
            ->get(route('team.index'))
            ->assertOk()
            ->assertSee('Teamrollen')
            ->assertSee('Administrator');
    }

    public function test_validation_messages_use_the_selected_language(): void
    {
        $this->withSession(['locale' => 'de'])
            ->from(route('register'))
            ->post(route('register.store'), [])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'name' => 'Name ist erforderlich.',
                'email' => 'E-Mail-Adresse ist erforderlich.',
                'password' => 'Passwort ist erforderlich.',
            ]);
    }

    public function test_unsupported_languages_are_rejected(): void
    {
        $this->from(route('login'))
            ->post(route('locale.update'), ['locale' => 'fr'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('locale');
    }
}
