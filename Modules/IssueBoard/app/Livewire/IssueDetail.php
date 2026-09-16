<?php

namespace Modules\IssueBoard\Livewire;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Models\IssueActivity;
use Modules\IssueBoard\Models\Label;
use Modules\IssueBoard\Notifications\IssueCommented;
use Modules\IssueBoard\Notifications\IssueMentioned;
use Modules\IssueBoard\Support\RichText;

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

    // Checklist fields
    public string $newChecklistTitle = '';

    // Label creation fields
    public string $newLabelName = '';
    public string $newLabelColor = '#f97316';

    // Time tracking fields
    public string $timeToLog = '';
    public string $newEstimate = '';

    public function mount(Issue $issue): void
    {
        $this->authorize('view', $issue);
        $this->issue = $issue;
    }

    public function setStatus(string $status): void
    {
        $target = IssueStatus::from($status);

        $this->authorize('moveTo', [$this->issue, $target]);

        $oldStatus = $this->issue->status->label();
        $this->issue->moveTo($target, auth()->user());
        
        $this->issue->logActivity(
            action: 'status_changed',
            userId: auth()->id(),
            field: 'status',
            fromValue: $oldStatus,
            toValue: $target->label()
        );

        $this->issue->refresh();
    }

    public function addChecklistItem(): void
    {
        $this->authorize('update', $this->issue);

        $title = trim($this->newChecklistTitle);
        if ($title === '') {
            return;
        }

        $item = $this->issue->checklistItems()->create([
            'title' => $title,
            'is_completed' => false,
            'position' => $this->issue->checklistItems()->count(),
        ]);

        $this->issue->logActivity(
            action: 'checklist_item_added',
            userId: auth()->id(),
            toValue: $title
        );

        $this->newChecklistTitle = '';
        $this->issue->refresh();
    }

    public function toggleChecklistItem(int $itemId): void
    {
        $item = $this->issue->checklistItems()->findOrFail($itemId);
        $item->update(['is_completed' => ! $item->is_completed]);

        $this->issue->logActivity(
            action: $item->is_completed ? 'checklist_item_completed' : 'checklist_item_uncompleted',
            userId: auth()->id(),
            toValue: $item->title
        );

        $this->issue->refresh();
    }

    public function deleteChecklistItem(int $itemId): void
    {
        $this->authorize('update', $this->issue);

        $item = $this->issue->checklistItems()->findOrFail($itemId);
        $title = $item->title;
        $item->delete();

        $this->issue->logActivity(
            action: 'checklist_item_deleted',
            userId: auth()->id(),
            fromValue: $title
        );

        $this->issue->refresh();
    }

    public function toggleLabel(int $labelId): void
    {
        $this->authorize('update', $this->issue);

        $label = Label::findOrFail($labelId);
        $this->issue->labels()->toggle($labelId);
        
        $attached = $this->issue->labels()->where('labels.id', $labelId)->exists();
        $this->issue->logActivity(
            action: $attached ? 'label_attached' : 'label_detached',
            userId: auth()->id(),
            toValue: $label->name
        );

        $this->issue->refresh();
    }

    public function createAndAttachLabel(): void
    {
        $this->authorize('update', $this->issue);

        $name = trim($this->newLabelName);
        if ($name === '') {
            return;
        }

        $user = auth()->user();
        $label = Label::firstOrCreate(
            [
                'name' => $name,
                'team_owner_id' => $user->teamOwnerId(),
            ],
            [
                'color' => $this->newLabelColor ?: '#f97316',
            ]
        );

        $this->issue->labels()->syncWithoutDetaching([$label->id]);
        $this->newLabelName = '';
        $this->issue->refresh();
    }

    public function logTime(): void
    {
        $hours = (float) $this->timeToLog;
        if ($hours <= 0) {
            return;
        }

        $this->issue->increment('spent_hours', $hours);
        $this->issue->logActivity(
            action: 'time_logged',
            userId: auth()->id(),
            toValue: "{$hours}h"
        );

        $this->timeToLog = '';
        $this->issue->refresh();
    }

    public function updateEstimate(): void
    {
        $this->authorize('update', $this->issue);

        $hours = (float) $this->newEstimate;
        if ($hours >= 0) {
            $this->issue->update(['estimated_hours' => $hours]);
            $this->issue->logActivity(
                action: 'estimate_updated',
                userId: auth()->id(),
                toValue: "{$hours}h"
            );
            $this->newEstimate = '';
            $this->issue->refresh();
        }
    }

    public function startReply(int $commentId): void
    {
        $this->authorize('comment', $this->issue);
        $this->issue->allComments()->findOrFail($commentId);

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
        $this->body = RichText::clean($this->body) ?? '';

        if ($this->replyTo) {
            $this->issue->allComments()->findOrFail($this->replyTo);
        }

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

        // Handle @mentions in comment body
        preg_match_all('/@([a-zA-Z0-9_\-\.]+)/', $this->body, $matches);
        if (! empty($matches[1])) {
            $mentionedUsernames = array_unique($matches[1]);
            $teamMembers = auth()->user()->teamMembers()->get();

            $mentionedUsers = $teamMembers->filter(function ($user) use ($mentionedUsernames) {
                foreach ($mentionedUsernames as $target) {
                    if (strcasecmp($user->name, $target) === 0 || strcasecmp(explode(' ', $user->name)[0], $target) === 0 || strcasecmp(explode('@', $user->email)[0], $target) === 0) {
                        return true;
                    }
                }
                return false;
            })->reject(fn ($u) => $u->id === auth()->id());

            if ($mentionedUsers->isNotEmpty()) {
                Notification::send($mentionedUsers, new IssueMentioned($comment));
            }
        }

        // Standard comment notification to other watchers
        $recipients = $this->issue->watchers()->reject(fn ($u) => $u->getKey() === auth()->id());
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new IssueCommented($comment));
        }

        $this->issue->logActivity(
            action: 'comment_added',
            userId: auth()->id(),
            toValue: $this->isQuestion ? 'Question' : 'Comment'
        );

        $this->reset('body', 'isQuestion', 'replyTo', 'commentFiles');
        $this->issue->refresh();
    }

    public function deleteComment(int $commentId): void
    {
        $this->authorize('comment', $this->issue);
        $comment = $this->issue->allComments()->findOrFail($commentId);

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
        $user = auth()->user();

        // Workspace labels for label picker
        $availableLabels = Label::query()
            ->where(function ($q) use ($user) {
                $q->where('team_owner_id', $user->teamOwnerId())
                  ->orWhereNull('team_owner_id');
            })
            ->orderBy('name')
            ->get();

        $activities = $this->issue->activities()->with('user')->latest()->take(20)->get();

        return view('issueboard::detail', [
            'issue' => $this->issue,
            'statuses' => IssueStatus::cases(),
            'comments' => $comments,
            'openQuestionCount' => $comments->filter->is_open_question->count(),
            'history' => $this->issue->statusLogs()->with('user')->limit(10)->get(),
            'availableLabels' => $availableLabels,
            'activities' => $activities,
            'body' => $this->body,
            'isQuestion' => $this->isQuestion,
            'replyTo' => $this->replyTo,
            'commentFiles' => $this->commentFiles,
            'newChecklistTitle' => $this->newChecklistTitle,
            'newLabelName' => $this->newLabelName,
            'newLabelColor' => $this->newLabelColor,
            'timeToLog' => $this->timeToLog,
            'newEstimate' => $this->newEstimate,
        ])->layout('issueboard::layouts.master');
    }
}
