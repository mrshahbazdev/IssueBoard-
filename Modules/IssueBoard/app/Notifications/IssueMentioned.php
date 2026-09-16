<?php

namespace Modules\IssueBoard\Notifications;

use App\Support\UserSmtpMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\IssueBoard\Models\IssueComment;

class IssueMentioned extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject(__('You were mentioned in :title', ['title' => $this->comment->issue->title]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('** :user ** mentioned you in a comment:', ['user' => $this->comment->user->name]))
            ->line(str($this->comment->body)->stripTags()->limit(400)->toString())
            ->action(__('View Issue'), route('issueboard.show', $this->comment->issue));

        return app(UserSmtpMailer::class)->apply($message, $this->comment->user);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mention',
            'issue_id' => $this->comment->issue_id,
            'issue_title' => $this->comment->issue->title,
            'comment_id' => $this->comment->id,
            'user_name' => $this->comment->user->name,
            'message' => "{$this->comment->user->name} mentioned you in an issue comment.",
        ];
    }
}
