<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->canManageTeam(), 403);

        $adminId = $request->user()->id;

        return view('team.index', [
            'users' => $request->user()->teamMembers()->orderBy('name')->get(),
            'roles' => UserRole::cases(),
            'invitations' => TeamInvitation::query()
                ->where('invited_by', $adminId)
                ->whereNull('accepted_at')
                ->latest()
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageTeam(), 403);

        if ($request->user()->is($user)) {
            return back()->withErrors(['role' => __('app.team.cannot_change_own_role')]);
        }

        // Verify the user belongs to this admin's team
        abort_unless($user->invited_by === $request->user()->id, 403);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        $user->update(['role' => $data['role']]);

        return back()->with('status', __('app.team.role_updated', ['name' => $user->name]));
    }
}
