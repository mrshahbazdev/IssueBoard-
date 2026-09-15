<?php

namespace Modules\IssueBoard\Notifications;

use App\Support\UserSmtpMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\IssueBoard\Models\IssueComment;

class IssueCommented extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public IssueComment $comment) {}

    public function via(object $notifiable): array
    {
        return $this->comment->user?->mailSetting()->exists()
            ? ['mail', 'database']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $key = $this->comment->is_question
            ? 'issueboard::issueboard.mail.question_subject'
            : 'issueboard::issueboard.mail.comment_subject';

        $message = (new MailMessage)
            ->subject(__($key, ['title' => $this->comment->issue->title]))
            ->greeting(__('issueboard::issueboard.mail.greeting', ['name' => $notifiable->name]))
            ->line($this->comment->user->name.':')
            ->line(str($this->comment->body)->stripTags()->limit(400)->toString())
            ->action(
                __('issueboard::issueboard.mail.answer'),
                route('issueboard.show', $this->comment->issue)
            );

        return app(UserSmtpMailer::class)->apply($message, $this->comment->user);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'issue_id' => $this->comment->issue_id,
            'comment_id' => $this->comment->id,
            'type' => $this->comment->is_question ? 'issue_question' : 'issue_comment',
        ];
    }
}
