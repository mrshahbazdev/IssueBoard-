<?php

namespace Modules\IssueBoard\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Notifications\IssueCreated;
use Modules\IssueBoard\Support\RichText;

class IssueForm extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?Issue $issue = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:20000')]
    public string $description = '';

    #[Validate('nullable|string|max:20000')]
    public string $suggested_solution = '';

    #[Validate('nullable|integer')]
    public $project_id = '';

    #[Validate('nullable|integer')]
    public $assigned_to = '';

    #[Validate('required|integer|between:1,3')]
    public int $priority = 2;

    #[Validate('nullable|date')]
    public $due_date = '';

    #[Validate('nullable|string|max:255')]
    public string $contact_name = '';

    #[Validate('nullable|email|max:255')]
    public string $contact_email = '';

    #[Validate('nullable|string|max:40')]
    public string $contact_phone = '';

    /** @var array<int, array{label: string, url: string}> */
    public array $links = [];

    public array $files = [];

    public function mount(?Issue $issue = null): void
    {
        $user = auth()->user();

        if ($issue?->exists) {
            $this->authorize('update', $issue);
            $this->issue = $issue;
            $this->fill($issue->only([
                'title', 'description', 'suggested_solution', 'project_id',
                'assigned_to', 'priority', 'contact_name', 'contact_email', 'contact_phone',
            ]));
            $this->due_date = $issue->due_date?->toDateString() ?? '';
            $this->links = $issue->links->map(fn ($l) => ['label' => $l->label ?? '', 'url' => $l->url])->all();
        } else {
            $this->authorize('create', Issue::class);
            $this->contact_name = $user->name ?? '';
            $this->contact_email = $user->email ?? '';
            $this->contact_phone = $user->phone ?? '';
        }

        if (! $this->links) {
            $this->links = [['label' => '', 'url' => '']];
        }
    }

    public function addLink(): void
    {
        $this->links[] = ['label' => '', 'url' => ''];
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function removeAttachment(int $attachmentId): void
    {
        $this->authorize('update', $this->issue);

        $attachment = $this->issue->attachments()->findOrFail($attachmentId);
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        $this->issue->refresh();
    }

    public function save()
    {
        $this->validate();

        $this->validate([
            'project_id' => 'nullable|integer|exists:projects,id',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'links.*.url' => 'nullable|url|max:2048',
            'links.*.label' => 'nullable|string|max:120',
            'files.*' => 'file|max:'.config('issueboard.max_upload_kb')
                .'|mimes:'.implode(',', config('issueboard.accepted_mimes')),
        ]);

        $isNew = ! $this->issue?->exists;

        $data = [
            'title' => $this->title,
            'description' => RichText::clean($this->description),
            'suggested_solution' => RichText::clean($this->suggested_solution),
            'project_id' => $this->project_id ?: null,
            'assigned_to' => $this->assigned_to ?: null,
            'priority' => $this->priority,
            'due_date' => $this->due_date ?: null,
            'contact_name' => $this->contact_name ?: null,
            'contact_email' => $this->contact_email ?: null,
            'contact_phone' => $this->contact_phone ?: null,
        ];

        if ($isNew) {
            $this->authorize('create', Issue::class);

            $this->issue = Issue::create([
                ...$data,
                'status' => IssueStatus::New,
                'created_by' => auth()->id(),
                'assigned_to' => $data['assigned_to'] ?: auth()->id(),
            ]);
        } else {
            $this->authorize('update', $this->issue);
            $this->issue->update($data);
        }

        $this->issue->links()->delete();

        foreach ($this->links as $link) {
            if (filled($link['url'])) {
                $this->issue->links()->create([
                    'label' => $link['label'] ?: null,
                    'url' => $link['url'],
                ]);
            }
        }

        foreach ($this->files as $file) {
            $this->issue->attachFile($file, auth()->id());
        }

        $this->files = [];

        if ($isNew) {
            $recipients = $this->issue->watchers()->reject(fn ($u) => $u->getKey() === auth()->id());

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new IssueCreated($this->issue));
            }
        }

        session()->flash('status', __('issueboard::issueboard.saved'));

        if (! $this->issue->isVisibleTo(auth()->user())) {
            return redirect()->route('issueboard.index');
        }

        return redirect()->route('issueboard.show', $this->issue);
    }

    public function render()
    {
        $userModel = config('issueboard.user_model');
        $projectModel = config('issueboard.project_model');

        return view('issueboard::form', [
            'users' => $userModel::where('role', '!=', 'viewer')->orderBy('name')->get(['id', 'name']),
            'projects' => class_exists($projectModel) ? $projectModel::orderBy('name')->get(['id', 'name']) : collect(),
        ])->layout('issueboard::layouts.master');
    }
}
