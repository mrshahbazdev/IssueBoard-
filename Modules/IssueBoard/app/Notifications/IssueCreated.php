<?php

namespace Modules\IssueBoard\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\IssueBoard\Models\Issue;

class IssueCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Issue $issue) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('issueboard::issueboard.mail.created_subject', ['title' => $this->issue->title]))
            ->greeting(__('issueboard::issueboard.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('issueboard::issueboard.mail.created_intro', [
                'author' => $this->issue->author?->name ?? '-',
                'project' => $this->issue->project?->name ?? __('issueboard::issueboard.all_projects'),
            ]))
            ->line('**'.$this->issue->title.'**');

        if ($this->issue->suggested_solution) {
            $mail->line(__('issueboard::issueboard.suggested_solution').': '.strip_tags($this->issue->suggested_solution));
        }

        if ($contact = $this->issue->contact_line) {
            $mail->line(__('issueboard::issueboard.contact').': '.$contact);
        }

        return $mail->action(
            __('issueboard::issueboard.mail.open_issue'),
            route('issueboard.show', $this->issue)
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'title' => $this->issue->title,
            'type' => 'issue_created',
        ];
    }
}
