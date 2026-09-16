<?php

namespace Modules\IssueBoard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Label extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'team_owner_id'];

    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'issue_label');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereNull('id');
        }

        $ownerId = $user->teamOwnerId();

        return $query->where(function ($q) use ($ownerId) {
            $q->where('team_owner_id', $ownerId)
                ->orWhereNull('team_owner_id');
        });
    }
}
