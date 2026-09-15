<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_the_board(): void
    {
        $user = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($user)
            ->get(route('issueboard.index'))
            ->assertOk();
    }

    public function test_viewers_have_read_only_issue_access(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $author = User::factory()->create(['role' => UserRole::Member]);
        $issue = Issue::create([
            'title' => 'Read-only check',
            'created_by' => $author->id,
            'status' => IssueStatus::New,
        ]);

        $this->actingAs($viewer)
            ->get(route('issueboard.show', $issue))
            ->assertOk()
            ->assertSee('read-only access');

        $this->actingAs($viewer)
            ->get(route('issueboard.create'))
            ->assertForbidden();
    }

    public function test_admin_can_manage_team_roles(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($admin)
            ->patch(route('team.role.update', $member), ['role' => UserRole::Developer->value])
            ->assertSessionHas('status', "{$member->name}'s role was updated.");

        $this->assertSame(UserRole::Developer, $member->refresh()->role);
    }

    public function test_non_admin_users_cannot_manage_team_roles(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($manager)
            ->get(route('team.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('team.role.update', $member), ['role' => UserRole::Developer->value])
            ->assertForbidden();
    }
}
