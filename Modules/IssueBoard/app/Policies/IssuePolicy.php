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
        return $issue->isVisibleTo($user);
    }

    public function create(Model $user): bool
    {
        return static::roleOf($user) !== UserRole::Viewer->value;
    }

    public function update(Model $user, Issue $issue): bool
    {
        if (! $this->view($user, $issue)) {
            return false;
        }

        $creator = $issue->author;
        $isCreatedByOwner = $creator && ($creator->role === UserRole::Admin || (int) $creator->id === (int) $issue->team_owner_id);

        // If the task was created by the workspace owner / admin:
        // ONLY the owner who created it can edit it. Team members CANNOT edit it!
        if ($isCreatedByOwner) {
            return (int) $user->getKey() === (int) $issue->created_by;
        }

        // For tasks created by regular members:
        // Workspace Admin can manage, or the member who created it can edit
        if (static::roleOf($user) === UserRole::Admin->value && (int) $issue->team_owner_id === (int) $user->teamOwnerId()) {
            return true;
        }

        return (int) $issue->created_by === (int) $user->getKey();
    }

    public function delete(Model $user, Issue $issue): bool
    {
        if (! $this->view($user, $issue)) {
            return false;
        }

        return (int) $issue->created_by === (int) $user->getKey()
            || (static::roleOf($user) === UserRole::Admin->value && (int) $issue->team_owner_id === (int) $user->teamOwnerId());
    }

    public function comment(Model $user, Issue $issue): bool
    {
        return $this->view($user, $issue)
            && static::roleOf($user) !== UserRole::Viewer->value;
    }

    public function moveTo(Model $user, Issue $issue, IssueStatus $status): bool
    {
        $allowed = config('issueboard.transitions')[static::roleOf($user)] ?? [];

        return $this->view($user, $issue)
            && (in_array('*', $allowed, true) || in_array($status->value, $allowed, true));
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
