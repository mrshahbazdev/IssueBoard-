<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;

class UserSmtpMailer
{
    public function apply(MailMessage $message, ?User $sender): MailMessage
    {
        $mailer = $sender ? $this->mailerFor($sender) : null;

        if (! $mailer || ! $sender?->mailSetting) {
            return $message;
        }

        return $message
            ->mailer($mailer)
            ->from(
                $sender->mailSetting->from_address,
                $sender->mailSetting->from_name ?: $sender->name
            );
    }

    public function mailerFor(User $sender): ?string
    {
        $setting = $sender->mailSetting()->first();

        if (! $setting) {
            return null;
        }

        $sender->setRelation('mailSetting', $setting);
        $name = 'user_smtp_'.$sender->getKey();

        config()->set("mail.mailers.{$name}", [
            'transport' => 'smtp',
            'scheme' => $setting->scheme,
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => $setting->username,
            'password' => $setting->password,
            'timeout' => 15,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);

        Mail::purge($name);

        return $name;
    }
}
