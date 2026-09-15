<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\UserSmtpMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SmtpSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_encrypted_personal_smtp_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.smtp'), $this->smtpData())
            ->assertSessionHas('status', 'SMTP settings saved.');

        $storedPassword = DB::table('user_mail_settings')
            ->where('user_id', $user->id)
            ->value('password');

        $this->assertNotSame('smtp-secret', $storedPassword);
        $this->assertSame('smtp-secret', $user->mailSetting()->firstOrFail()->password);
    }

    public function test_blank_password_keeps_the_existing_encrypted_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('profile.smtp'), $this->smtpData());
        $encryptedPassword = DB::table('user_mail_settings')->value('password');

        $this->put(route('profile.smtp'), [
            ...$this->smtpData(),
            'host' => 'smtp.changed.example.com',
            'password' => '',
        ])->assertSessionHasNoErrors();

        $this->assertSame($encryptedPassword, DB::table('user_mail_settings')->value('password'));
        $this->assertSame('smtp-secret', $user->mailSetting()->firstOrFail()->password);
    }

    public function test_personal_smtp_is_applied_to_notification_messages(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('profile.smtp'), $this->smtpData());

        $message = app(UserSmtpMailer::class)->apply(new MailMessage, $user->fresh());

        $this->assertSame('user_smtp_'.$user->id, $message->mailer);
        $this->assertSame(['sender@example.com', 'IssueBoard Sender'], $message->from);
        $this->assertSame('smtp.example.com', config("mail.mailers.{$message->mailer}.host"));
        $this->assertSame('smtp-secret', config("mail.mailers.{$message->mailer}.password"));
    }

    public function test_user_can_remove_personal_smtp_settings(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('profile.smtp'), $this->smtpData());

        $this->delete(route('profile.smtp.destroy'))
            ->assertSessionHas('status', 'SMTP settings removed.');

        $this->assertDatabaseMissing('user_mail_settings', ['user_id' => $user->id]);
    }

    private function smtpData(): array
    {
        return [
            'host' => 'smtp.example.com',
            'port' => 587,
            'scheme' => 'smtp',
            'username' => 'sender@example.com',
            'password' => 'smtp-secret',
            'from_address' => 'sender@example.com',
            'from_name' => 'IssueBoard Sender',
        ];
    }
}
