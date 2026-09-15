<?php

namespace Modules\IssueBoard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\IssueBoard\Enums\IssueStatus;

class IssueStatusLog extends Model
{
    protected $fillable = ['from_status', 'to_status', 'user_id'];

    protected function casts(): array
    {
        return [
            'from_status' => IssueStatus::class,
            'to_status' => IssueStatus::class,
        ];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('issueboard.user_model'));
    }
}
