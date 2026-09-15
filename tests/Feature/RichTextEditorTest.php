<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Livewire\IssueDetail;
use Modules\IssueBoard\Livewire\IssueForm;
use Modules\IssueBoard\Models\Issue;
use Tests\TestCase;

class RichTextEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_form_renders_the_rich_text_toolbar(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);

        $this->actingAs($user)
            ->get(route('issueboard.create'))
            ->assertOk()
            ->assertSee('contenteditable="true"', false)
            ->assertSee('aria-label="Bold"', false);
    }

    public function test_issue_content_is_sanitized_before_it_is_saved(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);

        Livewire::actingAs($user)
            ->test(IssueForm::class)
            ->set('title', 'Formatted task')
            ->set('description', '<p><strong>Keep this</strong><script>alert(1)</script></p>')
            ->call('save')
            ->assertRedirect();

        $description = Issue::firstOrFail()->description;

        $this->assertStringContainsString('<strong>Keep this</strong>', $description);
        $this->assertStringNotContainsString('<script', $description);
    }

    public function test_comment_content_is_sanitized_before_it_is_saved(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);
        $issue = Issue::create([
            'title' => 'Comment formatting',
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'status' => IssueStatus::New,
        ]);

        Livewire::actingAs($user)
            ->test(IssueDetail::class, ['issue' => $issue])
            ->set('body', '<p><em>Useful note</em><img src=x onerror=alert(1)></p>')
            ->call('addComment')
            ->assertHasNoErrors();

        $body = $issue->allComments()->firstOrFail()->body;

        $this->assertStringContainsString('<em>Useful note</em>', $body);
        $this->assertStringNotContainsString('<img', $body);
        $this->assertStringNotContainsString('onerror', $body);
    }
}
