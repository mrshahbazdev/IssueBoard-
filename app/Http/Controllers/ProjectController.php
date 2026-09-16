<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $projects = Project::query()
            ->visibleTo($user)
            ->withCount('issues')
            ->orderBy('name')
            ->get();

        return view('projects.index', [
            'projects' => $projects,
            'canManage' => $user->canManageTeam(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageTeam(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#0f766e',
            'team_owner_id' => $request->user()->teamOwnerId(),
        ]);

        return back()->with('status', __('app.projects.created', ['name' => $project->name]));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->canManageTeam(), 403);
        abort_unless((int) $project->team_owner_id === (int) $request->user()->teamOwnerId() || is_null($project->team_owner_id), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $project->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? $project->color,
        ]);

        return back()->with('status', __('app.projects.updated', ['name' => $project->name]));
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->canManageTeam(), 403);
        abort_unless((int) $project->team_owner_id === (int) $request->user()->teamOwnerId() || is_null($project->team_owner_id), 403);

        $name = $project->name;
        $project->issues()->update(['project_id' => null]);
        $project->delete();

        return back()->with('status', __('app.projects.deleted', ['name' => $name]));
    }
}
