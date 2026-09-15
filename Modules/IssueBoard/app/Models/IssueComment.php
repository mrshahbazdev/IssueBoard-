<?php

namespace Modules\IssueBoard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\IssueBoard\Support\HasAttachments;

class IssueComment extends Model
{
    use HasAttachments;

    protected $fillable = ['issue_id', 'parent_id', 'user_id', 'body', 'is_question', 'answered_at'];

    protected function casts(): array
    {
        return [
            'is_question' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (IssueComment $comment) {
            if ($comment->parent_id) {
                $comment->parent()->whereNull('answered_at')->update(['answered_at' => now()]);
            }
        });

        static::deleting(fn (IssueComment $comment) => $comment->deleteAttachments());
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest()->with('user', 'attachments');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('issueboard.user_model'));
    }

    public function getIsOpenQuestionAttribute(): bool
    {
        return $this->is_question && ! $this->answered_at;
    }
}
