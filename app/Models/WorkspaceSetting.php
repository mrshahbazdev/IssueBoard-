<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_owner_id',
        'company_name',
        'logo_path',
        'accent_color',
    ];

    public function teamOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_owner_id');
    }

    public static function forUser(?User $user): ?self
    {
        if (! $user) {
            return null;
        }

        return static::where('team_owner_id', $user->teamOwnerId())->first();
    }
}
