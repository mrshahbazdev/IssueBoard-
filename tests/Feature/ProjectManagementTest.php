<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\IssueBoard\Models\Issue;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_create_project(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee(__('app.projects.create_heading'));

        $response = $this->actingAs($admin)
            ->post(route('projects.store'), [
                'name' => 'Internal Redesign',
                'description' => 'Revamp the core UI',
                'color' => '#f97316',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', [
            'name' => 'Internal Redesign',
            'team_owner_id' => $admin->id,
            'color' => '#f97316',
        ]);
    }

    public function test_team_scoping_isolates_projects_between_different_admins(): void
    {
        $adminA = User::factory()->create(['role' => UserRole::Admin]);
        $adminB = User::factory()->create(['role' => UserRole::Admin]);

        $projectA = Project::create([
            'name' => 'Project of Team A',
            'team_owner_id' => $adminA->id,
            'color' => '#10b981',
        ]);

        $projectB = Project::create([
            'name' => 'Project of Team B',
            'team_owner_id' => $adminB->id,
            'color' => '#6366f1',
        ]);

        $this->actingAs($adminA)
            ->get(route('projects.index'))
            ->assertSee('Project of Team A')
            ->assertDontSee('Project of Team B');

        $this->actingAs($adminB)
            ->get(route('projects.index'))
            ->assertSee('Project of Team B')
            ->assertDontSee('Project of Team A');

        // Admin A cannot update Admin B's project
        $this->actingAs($adminA)
            ->put(route('projects.update', $projectB), ['name' => 'Hacked Project'])
            ->assertForbidden();

        // Admin A cannot delete Admin B's project
        $this->actingAs($adminA)
            ->delete(route('projects.destroy', $projectB))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_create_update_or_delete_project(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $developer = User::factory()->create([
            'role' => UserRole::Developer,
            'team_owner_id' => $admin->id,
        ]);

        $project = Project::create([
            'name' => 'Admin Project',
            'team_owner_id' => $admin->id,
            'color' => '#0f766e',
        ]);

        // Developer can view
        $this->actingAs($developer)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Admin Project')
            ->assertDontSee(__('app.projects.create_heading'));

        // Developer cannot store
        $this->actingAs($developer)
            ->post(route('projects.store'), ['name' => 'Dev Project'])
            ->assertForbidden();

        // Developer cannot update
        $this->actingAs($developer)
            ->put(route('projects.update', $project), ['name' => 'Updated by Dev'])
            ->assertForbidden();

        // Developer cannot delete
        $this->actingAs($developer)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();
    }

    public function test_admin_can_update_and_delete_project_unlinking_issues(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $project = Project::create([
            'name' => 'Old Name',
            'team_owner_id' => $admin->id,
            'color' => '#0f766e',
        ]);

        $issue = Issue::create([
            'title' => 'Issue in project',
            'team_owner_id' => $admin->id,
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'project_id' => $project->id,
        ]);

        // Update
        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'name' => 'New Name',
                'description' => 'Updated Description',
                'color' => '#ef4444',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'New Name',
            'color' => '#ef4444',
        ]);

        // Delete
        $this->actingAs($admin)
            ->delete(route('projects.destroy', $project))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);

        // Issue must still exist, but project_id must be null
        $issue->refresh();
        $this->assertNull($issue->project_id);
    }
}
