<?php

namespace Modules\IssueBoard\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;

class IssueStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Issue $issue,
        public IssueStatus $from,
        public IssueStatus $to,
        public Model $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('issueboard::issueboard.mail.status_subject', [
                'title' => $this->issue->title,
                'status' => $this->to->label(),
            ]))
            ->greeting(__('issueboard::issueboard.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('issueboard::issueboard.mail.status_intro', [
                'actor' => $this->actor->name,
                'from' => $this->from->label(),
                'to' => $this->to->label(),
            ]))
            ->line('**'.$this->issue->title.'**')
            ->action(__('issueboard::issueboard.mail.open_issue'), route('issueboard.show', $this->issue));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'from' => $this->from->value,
            'to' => $this->to->value,
            'type' => 'issue_status_changed',
        ];
    }
}
