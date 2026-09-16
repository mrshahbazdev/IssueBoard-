<?php

namespace Modules\IssueBoard\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Models\Label;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Board extends Component
{
    use AuthorizesRequests;

    #[Url(as: 'project', except: '')]
    public string $projectId = '';

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'mine', except: false)]
    public bool $onlyMine = false;

    #[Url(as: 'priority', except: '')]
    public string $priority = '';

    #[Url(as: 'assignee', except: '')]
    public string $assigneeId = '';

    #[Url(as: 'label', except: '')]
    public string $labelId = '';

    #[Url(as: 'view', except: 'board')]
    public string $viewMode = 'board';

    public string $calendarMonth = '';

    public function mount(): void
    {
        if (! $this->calendarMonth) {
            $this->calendarMonth = now()->format('Y-m');
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['board', 'calendar'], true) ? $mode : 'board';
    }

    public function previousMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->calendarMonth = Carbon::parse($this->calendarMonth . '-01')->addMonth()->format('Y-m');
    }

    public function currentMonth(): void
    {
        $this->calendarMonth = now()->format('Y-m');
    }

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
        $this->reset('projectId', 'search', 'onlyMine', 'priority', 'assigneeId', 'labelId');
    }

    public function exportCsv(): StreamedResponse
    {
        $user = auth()->user();
        $issues = $this->buildBaseQuery()->with(['project', 'assignee', 'author'])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="issueboard-export-' . now()->format('Y-m-d-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($issues) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Title',
                'Status',
                'Priority',
                'Project',
                'Assignee',
                'Created By',
                'Due Date',
                'Estimated Hours',
                'Spent Hours',
                'Created At',
            ]);

            foreach ($issues as $issue) {
                fputcsv($handle, [
                    $issue->id,
                    $issue->title,
                    $issue->status->label(),
                    $issue->priority_label,
                    $issue->project?->name ?? 'None',
                    $issue->assignee?->name ?? 'Unassigned',
                    $issue->author?->name ?? 'System',
                    $issue->due_date ? $issue->due_date->format('Y-m-d') : '',
                    $issue->estimated_hours ?? 0,
                    $issue->spent_hours ?? 0,
                    $issue->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function buildBaseQuery()
    {
        return Issue::query()
            ->visibleTo(auth()->user())
            ->forProject($this->projectId ? (int) $this->projectId : null)
            ->search($this->search)
            ->when($this->onlyMine, fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($this->priority !== '', fn ($q) => $q->where('priority', (int) $this->priority))
            ->when($this->assigneeId !== '', function ($q) {
                if ($this->assigneeId === 'unassigned') {
                    $q->whereNull('assigned_to');
                } else {
                    $q->where('assigned_to', (int) $this->assigneeId);
                }
            })
            ->when($this->labelId !== '', fn ($q) => $q->whereHas('labels', fn ($lq) => $lq->where('labels.id', (int) $this->labelId)));
    }

    public function render()
    {
        $user = auth()->user();
        $baseQuery = $this->buildBaseQuery();

        $issues = (clone $baseQuery)
            ->with(['project', 'assignee', 'author', 'attachments', 'labels'])
            ->withBoardCounts()
            ->orderBy('position')
            ->latest('id')
            ->get()
            ->groupBy(fn (Issue $issue) => $issue->status->value);

        $projectModel = config('issueboard.project_model');
        $projects = class_exists($projectModel) ? $projectModel::query()->visibleTo($user)->orderBy('name')->get(['id', 'name']) : collect();

        // Team members for filter dropdown
        $teamMembers = $user->teamMembers()->orderBy('name')->get(['id', 'name']);

        // Labels for filter dropdown
        $labels = Label::query()
            ->where(function ($q) use ($user) {
                $q->where('team_owner_id', $user->teamOwnerId())
                  ->orWhereNull('team_owner_id');
            })
            ->orderBy('name')
            ->get();

        // Calendar data if calendar view is active
        $calendarDays = [];
        $calendarTitle = '';
        if ($this->viewMode === 'calendar') {
            $monthDate = Carbon::parse($this->calendarMonth . '-01');
            $calendarTitle = $monthDate->translatedFormat('F Y');
            $startOfMonth = $monthDate->copy()->startOfMonth();
            $endOfMonth = $monthDate->copy()->endOfMonth();

            $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

            // Fetch issues with due dates in grid window
            $calendarIssues = (clone $baseQuery)
                ->whereBetween('due_date', [$startOfGrid->format('Y-m-d'), $endOfGrid->format('Y-m-d')])
                ->with(['project', 'assignee', 'labels'])
                ->get()
                ->groupBy(fn (Issue $i) => $i->due_date->format('Y-m-d'));

            $day = $startOfGrid->copy();
            while ($day->lte($endOfGrid)) {
                $dateStr = $day->format('Y-m-d');
                $calendarDays[] = [
                    'date' => $day->copy(),
                    'isCurrentMonth' => $day->month === $monthDate->month,
                    'isToday' => $day->isToday(),
                    'issues' => $calendarIssues->get($dateStr, collect()),
                ];
                $day->addDay();
            }
        }

        $hasFilters = filled($this->search)
            || filled($this->projectId)
            || $this->onlyMine
            || filled($this->priority)
            || filled($this->assigneeId)
            || filled($this->labelId);

        return view('issueboard::board', [
            'statuses' => IssueStatus::cases(),
            'issues' => $issues,
            'projects' => $projects,
            'teamMembers' => $teamMembers,
            'labels' => $labels,
            'hasFilters' => $hasFilters,
            'calendarDays' => $calendarDays,
            'calendarTitle' => $calendarTitle,
            'viewMode' => $this->viewMode,
        ])->layout('issueboard::layouts.master');
    }
}
