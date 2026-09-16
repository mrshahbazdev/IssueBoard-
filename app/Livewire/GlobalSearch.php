<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\IssueBoard\Models\Issue;

class GlobalSearch extends Component
{
    public string $query = '';
    public bool $isOpen = false;

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function render()
    {
        $issues = collect();
        $projects = collect();
        $users = collect();

        $clean = trim($this->query);

        if (mb_strlen($clean) >= 2) {
            $user = Auth::user();

            // Search Issues
            $issues = Issue::query()
                ->visibleTo($user)
                ->where(function ($q) use ($clean) {
                    $q->where('title', 'like', "%{$clean}%")
                      ->orWhere('suggested_solution', 'like', "%{$clean}%");
                    
                    if (is_numeric(ltrim($clean, '#'))) {
                        $q->orWhere('id', (int) ltrim($clean, '#'));
                    }
                })
                ->with(['project', 'statusLogs'])
                ->latest('id')
                ->take(6)
                ->get();

            // Search Projects
            $projectModel = config('issueboard.project_model');
            if (class_exists($projectModel)) {
                $projects = $projectModel::query()
                    ->visibleTo($user)
                    ->where('name', 'like', "%{$clean}%")
                    ->take(4)
                    ->get();
            }

            // Search Team Members
            if ($user) {
                $users = User::query()
                    ->where(function ($q) use ($user) {
                        $q->where('id', $user->workspaceOwnerId())
                          ->orWhere('invited_by', $user->workspaceOwnerId());
                    })
                    ->where(function ($q) use ($clean) {
                        $q->where('name', 'like', "%{$clean}%")
                          ->orWhere('email', 'like', "%{$clean}%");
                    })
                    ->take(4)
                    ->get();
            }
        }

        return view('livewire.global-search', [
            'issues' => $issues,
            'projects' => $projects,
            'users' => $users,
        ]);
    }
}
