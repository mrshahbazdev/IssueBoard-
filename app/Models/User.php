<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'invited_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function canManageTeam(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function inviter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'invited_by');
    }

    public function invitedUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'invited_by');
    }

    public function teamOwnerId(): int
    {
        return $this->invited_by ?? $this->getKey();
    }

    public function teamMembers(): \Illuminate\Database\Eloquent\Builder
    {
        $ownerId = $this->teamOwnerId();

        return static::query()->where(function ($query) use ($ownerId) {
            $query->where('id', $ownerId)
                ->orWhere('invited_by', $ownerId);
        });
    }

    public function mailSetting(): HasOne
    {
        return $this->hasOne(UserMailSetting::class);
    }
}
