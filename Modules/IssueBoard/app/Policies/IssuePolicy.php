<?php

namespace Modules\IssueBoard\Policies;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;

class IssuePolicy
{
    public function viewAny(Model $user): bool
    {
        return true;
    }

    public function view(Model $user, Issue $issue): bool
    {
        return true;
    }

    public function create(Model $user): bool
    {
        return static::roleOf($user) !== UserRole::Viewer->value;
    }

    public function update(Model $user, Issue $issue): bool
    {
        return in_array(static::roleOf($user), [
            UserRole::Admin->value,
            UserRole::Manager->value,
            UserRole::Developer->value,
        ], true)
            || $issue->created_by === $user->getKey();
    }

    public function delete(Model $user, Issue $issue): bool
    {
        return in_array(static::roleOf($user), [UserRole::Admin->value, UserRole::Manager->value], true);
    }

    public function comment(Model $user, Issue $issue): bool
    {
        return static::roleOf($user) !== UserRole::Viewer->value;
    }

    public function moveTo(Model $user, Issue $issue, IssueStatus $status): bool
    {
        $allowed = config('issueboard.transitions')[static::roleOf($user)] ?? [];

        return in_array('*', $allowed, true) || in_array($status->value, $allowed, true);
    }

    public static function roleOf(Model $user): string
    {
        if ($resolver = config('issueboard.role_resolver')) {
            return $resolver($user);
        }

        return $user->role instanceof UserRole
            ? $user->role->value
            : ($user->role ?? UserRole::Member->value);
    }
}
