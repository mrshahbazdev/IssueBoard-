<?php

namespace Modules\IssueBoard\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;

class Board extends Component
{
    use AuthorizesRequests;

    #[Url(as: 'project', except: '')]
    public string $projectId = '';

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'mine', except: false)]
    public bool $onlyMine = false;

    /** @param array<int> $orderedIds */
    public function moveCard(int $issueId, string $status, array $orderedIds = []): void
    {
        $issue = Issue::findOrFail($issueId);
        $target = IssueStatus::from($status);

        if (! auth()->user()->can('moveTo', [$issue, $target])) {
            $this->dispatch('board-error', message: __('issueboard::issueboard.errors.not_allowed'));
            $this->dispatch('$refresh');

            return;
        }

        $issue->moveTo($target, auth()->user());

        if ($orderedIds) {
            $user = auth()->user();

            DB::transaction(function () use ($orderedIds, $user) {
                foreach ($orderedIds as $position => $id) {
                    Issue::query()
                        ->visibleTo($user)
                        ->whereKey($id)
                        ->update(['position' => $position]);
                }
            });
        }

        $this->dispatch('board-updated', message: __('issueboard::issueboard.moved', [
            'status' => $target->label(),
        ]));
    }

    #[On('issue-saved')]
    #[On('issue-deleted')]
    public function refreshBoard(): void
    {
        // Livewire re-renders; kept explicit so child components can trigger it.
    }

    public function resetFilters(): void
    {
        $this->reset('projectId', 'search', 'onlyMine');
    }

    public function render()
    {
        $issues = Issue::query()
            ->visibleTo(auth()->user())
            ->forProject($this->projectId ? (int) $this->projectId : null)
            ->search($this->search)
            ->when($this->onlyMine, fn ($q) => $q->where('assigned_to', auth()->id()))
            ->with(['project', 'assignee', 'author', 'attachments'])
            ->withBoardCounts()
            ->orderBy('position')
            ->latest('id')
            ->get()
            ->groupBy(fn (Issue $issue) => $issue->status->value);

        $projectModel = config('issueboard.project_model');

        return view('issueboard::board', [
            'statuses' => IssueStatus::cases(),
            'issues' => $issues,
            'projects' => class_exists($projectModel) ? $projectModel::orderBy('name')->get(['id', 'name']) : collect(),
            'hasFilters' => filled($this->search) || filled($this->projectId) || $this->onlyMine,
        ])->layout('issueboard::layouts.master');
    }
}
