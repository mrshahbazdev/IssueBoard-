<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DeploymentRouteFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_remains_available_when_smtp_routes_are_missing_from_the_route_cache(): void
    {
        $user = User::factory()->create();

        $this->removeNamedRoutes([
            'profile.smtp',
            'profile.smtp.test',
            'profile.smtp.destroy',
        ]);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Email settings are temporarily unavailable');
    }

    public function test_team_page_remains_available_when_invitation_routes_are_missing_from_the_route_cache(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->removeNamedRoutes([
            'team.invitations.store',
            'team.invitations.resend',
            'team.invitations.destroy',
        ]);

        $this->actingAs($admin)
            ->get('/team')
            ->assertOk()
            ->assertSee('Team invitations are temporarily unavailable');
    }

    /**
     * @param  list<string>  $names
     */
    private function removeNamedRoutes(array $names): void
    {
        $routes = new RouteCollection;

        foreach (Route::getRoutes() as $route) {
            if (! in_array($route->getName(), $names, true)) {
                $routes->add($route);
            }
        }

        Route::setRoutes($routes);
    }
}
