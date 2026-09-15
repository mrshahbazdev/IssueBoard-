<?php

namespace Modules\IssueBoard\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Models\IssueComment;
use Modules\IssueBoard\Notifications\IssueCommented;

class IssueDetail extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Issue $issue;

    #[Validate('required|string|max:10000')]
    public string $body = '';

    public bool $isQuestion = false;

    public ?int $replyTo = null;

    public array $commentFiles = [];

    public function mount(Issue $issue): void
    {
        $this->authorize('view', $issue);
        $this->issue = $issue;
    }

    public function setStatus(string $status): void
    {
        $target = IssueStatus::from($status);

        $this->authorize('moveTo', [$this->issue, $target]);

        $this->issue->moveTo($target, auth()->user());
        $this->issue->refresh();
    }

    public function startReply(int $commentId): void
    {
        $this->authorize('comment', $this->issue);

        $this->replyTo = $commentId;
        $this->isQuestion = false;
        $this->dispatch('focus-comment-box');
    }

    public function cancelReply(): void
    {
        $this->replyTo = null;
    }

    public function markAnswered(int $commentId): void
    {
        $this->authorize('comment', $this->issue);

        $this->issue->allComments()
            ->whereKey($commentId)
            ->update(['answered_at' => now()]);

        $this->issue->refresh();
    }

    public function addComment(): void
    {
        $this->authorize('comment', $this->issue);

        $this->validate([
            'body' => 'required|string|max:10000',
            'commentFiles.*' => 'file|max:'.config('issueboard.max_upload_kb')
                .'|mimes:'.implode(',', config('issueboard.accepted_mimes')),
        ]);

        $comment = $this->issue->allComments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $this->replyTo,
            'body' => $this->body,
            'is_question' => $this->isQuestion && ! $this->replyTo,
        ]);

        foreach ($this->commentFiles as $file) {
            $comment->attachFile($file, auth()->id());
        }

        $recipients = $this->issue->watchers()->reject(fn ($u) => $u->getKey() === auth()->id());

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new IssueCommented($comment));
        }

        $this->reset('body', 'isQuestion', 'replyTo', 'commentFiles');
        $this->issue->refresh();
    }

    public function deleteComment(int $commentId): void
    {
        $comment = IssueComment::findOrFail($commentId);

        abort_unless(
            $comment->user_id === auth()->id() || auth()->user()->can('delete', $this->issue),
            403
        );

        $comment->delete();
        $this->issue->refresh();
    }

    public function render()
    {
        $comments = $this->issue->comments()->with('user', 'replies', 'attachments')->get();

        return view('issueboard::detail', [
            'statuses' => IssueStatus::cases(),
            'comments' => $comments,
            'openQuestionCount' => $comments->filter->is_open_question->count(),
            'history' => $this->issue->statusLogs()->with('user')->limit(10)->get(),
        ])->layout('issueboard::layouts.master');
    }
}
