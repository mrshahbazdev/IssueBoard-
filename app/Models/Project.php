<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\IssueBoard\Models\Issue;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'color', 'team_owner_id', 'created_by'];

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function teamOwner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'team_owner_id');
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (int) $this->team_owner_id === (int) $user->teamOwnerId()
            || (int) $this->created_by === (int) $user->getKey();
    }

    public function scopeVisibleTo(\Illuminate\Database\Eloquent\Builder $query, ?User $user): \Illuminate\Database\Eloquent\Builder
    {
        if (! $user) {
            return $query->whereNull('id');
        }

        $ownerId = $user->teamOwnerId();

        return $query->where(function ($q) use ($user, $ownerId) {
            $q->where('team_owner_id', $ownerId)
              ->orWhere('created_by', $user->getKey());
        });
    }
}
