<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Livewire\IssueForm;
use Modules\IssueBoard\Mail\IssueDigest;
use Modules\IssueBoard\Models\Issue;
use Tests\TestCase;

class IssueVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_only_see_issues_assigned_to_them(): void
    {
        $assignee = User::factory()->create(['role' => UserRole::Developer]);
        $otherUser = User::factory()->create(['role' => UserRole::Developer]);
        $author = User::factory()->create();

        $visibleIssue = $this->issue('Assigned issue', $author, $assignee);
        $hiddenIssue = $this->issue('Private issue', $author, $otherUser);

        $this->actingAs($assignee)
            ->get(route('issueboard.index'))
            ->assertOk()
            ->assertSee($visibleIssue->title)
            ->assertDontSee($hiddenIssue->title);

        $this->actingAs($assignee)
            ->get(route('issueboard.show', $visibleIssue))
            ->assertOk();

        $this->actingAs($assignee)
            ->get(route('issueboard.show', $hiddenIssue))
            ->assertForbidden();

        $this->actingAs($assignee)
            ->get(route('issueboard.edit', $hiddenIssue))
            ->assertForbidden();

        $this->assertSame([$assignee->id], $visibleIssue->watchers()->pluck('id')->all());
    }

    public function test_admins_and_managers_can_see_all_issues(): void
    {
        $assignee = User::factory()->create();
        $author = User::factory()->create();
        $issue = $this->issue('Workspace issue', $author, $assignee);

        foreach ([UserRole::Admin, UserRole::Manager] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('issueboard.index'))
                ->assertOk()
                ->assertSee($issue->title);

            $this->actingAs($user)
                ->get(route('issueboard.show', $issue))
                ->assertOk();
        }
    }

    public function test_unassigned_issues_are_not_visible_to_regular_users(): void
    {
        $author = User::factory()->create();
        $issue = $this->issue('Unassigned issue', $author);

        $this->actingAs($author)
            ->get(route('issueboard.show', $issue))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('issueboard.show', $issue))
            ->assertOk();
    }

    public function test_new_issues_default_to_the_creator_when_no_assignee_is_selected(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);

        Livewire::actingAs($user)
            ->test(IssueForm::class)
            ->set('title', 'Self-assigned task')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas((new Issue)->getTable(), [
            'title' => 'Self-assigned task',
            'assigned_to' => $user->id,
        ]);
    }

    public function test_digest_only_sends_each_regular_user_visible_issues(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $admin->mailSetting()->create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'scheme' => 'smtp',
            'username' => 'mailer@example.com',
            'password' => 'secret-password',
            'from_address' => 'mailer@example.com',
            'from_name' => 'IssueBoard Mailer',
        ]);

        $assignee = User::factory()->create(['role' => UserRole::Developer]);
        $otherDeveloper = User::factory()->create(['role' => UserRole::Developer]);
        $issue = $this->issue('Private digest issue', $admin, $assignee);

        $this->artisan('issueboard:digest')->assertSuccessful();

        Mail::assertSent(IssueDigest::class, function (IssueDigest $mail) use ($admin, $issue): bool {
            return $mail->hasTo($admin->email) && $mail->newIssues->contains($issue);
        });
        Mail::assertSent(IssueDigest::class, function (IssueDigest $mail) use ($assignee, $issue): bool {
            return $mail->hasTo($assignee->email) && $mail->newIssues->contains($issue);
        });
        Mail::assertNotSent(IssueDigest::class, function (IssueDigest $mail) use ($otherDeveloper): bool {
            return $mail->hasTo($otherDeveloper->email);
        });
    }

    private function issue(string $title, User $author, ?User $assignee = null): Issue
    {
        return Issue::create([
            'title' => $title,
            'created_by' => $author->id,
            'assigned_to' => $assignee?->id,
            'status' => IssueStatus::New,
        ]);
    }
}
