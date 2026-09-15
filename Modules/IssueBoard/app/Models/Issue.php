<?php

namespace Modules\IssueBoard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Notifications\IssueStatusChanged;
use Modules\IssueBoard\Support\HasAttachments;

class Issue extends Model
{
    use HasAttachments;
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'title', 'description', 'suggested_solution',
        'status', 'priority', 'position', 'created_by', 'assigned_to',
        'contact_name', 'contact_email', 'contact_phone', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => IssueStatus::class,
            'due_date' => 'date',
            'closed_at' => 'datetime',
            'priority' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Issue $issue) {
            if ($issue->isForceDeleting()) {
                $issue->deleteAttachments();
            }
        });
    }

    public function links(): HasMany
    {
        return $this->hasMany(IssueLink::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IssueComment::class)
            ->whereNull('parent_id')
            ->oldest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(IssueComment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(IssueStatusLog::class)->latest();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('issueboard.user_model'), 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(config('issueboard.user_model'), 'assigned_to');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(config('issueboard.project_model'), 'project_id');
    }

    // ------------------------------------------------------------------ scopes

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', IssueStatus::Done->value);
    }

    public function scopeForProject(Builder $query, ?int $projectId): Builder
    {
        return $projectId
            ? $query->where(fn ($q) => $q->where('project_id', $projectId)->orWhereNull('project_id'))
            : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('contact_name', 'like', "%{$term}%");
        });
    }

    public function scopeWithBoardCounts(Builder $query): Builder
    {
        return $query->withCount([
            'allComments as comments_count',
            'allComments as open_questions_count' => fn ($q) => $q->where('is_question', true)->whereNull('answered_at'),
            'attachments as attachments_count',
        ]);
    }

    // ----------------------------------------------------------------- actions

    public function moveTo(IssueStatus $to, Model $user, bool $notify = true): void
    {
        $from = $this->status;

        if ($from === $to) {
            return;
        }

        $this->forceFill([
            'status' => $to,
            'closed_at' => $to === IssueStatus::Done ? now() : null,
        ])->save();

        $this->statusLogs()->create([
            'from_status' => $from->value,
            'to_status' => $to->value,
            'user_id' => $user->getKey(),
        ]);

        if ($notify) {
            $recipients = $this->watchers()->reject(fn ($w) => $w->getKey() === $user->getKey());

            if ($recipients->isNotEmpty()) {
                Notification::send(
                    $recipients,
                    new IssueStatusChanged($this, $from, $to, $user)
                );
            }
        }
    }

    /** Everyone who should hear about this issue: author, assignee, commenters. */
    public function watchers(): Collection
    {
        $userModel = config('issueboard.user_model');

        $ids = collect([$this->created_by, $this->assigned_to])
            ->merge($this->allComments()->pluck('user_id'))
            ->filter()
            ->unique();

        return $ids->isEmpty()
            ? collect()
            : $userModel::whereIn((new $userModel)->getKeyName(), $ids)->get();
    }

    // -------------------------------------------------------------- accessors

    public function getPriorityLabelAttribute(): string
    {
        return __('issueboard::issueboard.priority.'.$this->priority);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->status !== IssueStatus::Done
            && $this->due_date->isPast();
    }

    public function getContactLineAttribute(): ?string
    {
        return collect([$this->contact_name, $this->contact_email, $this->contact_phone])
            ->filter()
            ->implode(' · ') ?: null;
    }
}
