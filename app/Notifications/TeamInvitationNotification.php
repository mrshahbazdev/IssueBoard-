<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use App\Support\UserSmtpMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TeamInvitation $invitation,
        public string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('app.invitation.mail_subject'))
            ->greeting(__('app.invitation.mail_greeting'))
            ->line(__('app.invitation.mail_intro', [
                'name' => $this->invitation->inviter?->name ?? config('app.name'),
            ]))
            ->line(__('app.invitation.mail_role', [
                'role' => $this->invitation->role->label(),
            ]))
            ->action(
                __('app.invitation.mail_action'),
                route('invitations.accept', ['token' => $this->token])
            )
            ->line(__('app.invitation.mail_expires', [
                'days' => config('auth.invitation_expire_days'),
            ]));

        return app(UserSmtpMailer::class)->apply($message, $this->invitation->inviter);
    }
}
