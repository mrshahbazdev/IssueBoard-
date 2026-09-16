<?php

namespace Modules\IssueBoard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'user_id',
        'action',
        'description',
        'from_value',
        'to_value',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(Issue $issue, string $action, ?string $description = null, ?string $from = null, ?string $to = null, ?int $userId = null): self
    {
        return static::create([
            'issue_id' => $issue->id,
            'user_id' => $userId ?: auth()->id() ?: $issue->created_by,
            'action' => $action,
            'description' => $description,
            'from_value' => $from,
            'to_value' => $to,
        ]);
    }
}
