<?php

namespace Modules\IssueBoard\Console;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\UserSmtpMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Mail\IssueDigest;
use Modules\IssueBoard\Models\Issue;
use Modules\IssueBoard\Models\IssueComment;

class SendIssueDigest extends Command
{
    protected $signature = 'issueboard:digest {--dry-run : Preview recipients without sending}';

    protected $description = 'Sends the weekly summary of open tasks to responsible team roles';

    public function handle(UserSmtpMailer $smtpMailer): int
    {
        if (! config('issueboard.digest.enabled')) {
            $this->info('The digest is disabled.');

            return self::SUCCESS;
        }

        $sender = User::query()
            ->whereIn('role', [UserRole::Admin->value, UserRole::Manager->value])
            ->whereHas('mailSetting')
            ->first();

        if (! $sender && ! $this->option('dry-run')) {
            $this->error('No Admin or Manager has configured SMTP settings.');

            return self::FAILURE;
        }

        $sent = false;
        foreach ($this->recipients() as $user) {
            $base = Issue::query()
                ->visibleTo($user)
                ->with('project', 'assignee', 'author');

            $newIssues = (clone $base)->where('status', IssueStatus::New->value)->latest()->get();
            $inReview = (clone $base)->where('status', IssueStatus::Review->value)->latest()->get();
            $overdue = (clone $base)->open()->whereDate('due_date', '<', now())->get();
            $openQuestions = IssueComment::with('issue', 'user')
                ->whereHas('issue', fn ($query) => $query->visibleTo($user))
                ->where('is_question', true)
                ->whereNull('answered_at')
                ->latest()
                ->get();

            if ($newIssues->isEmpty() && $inReview->isEmpty() && $openQuestions->isEmpty() && $overdue->isEmpty()) {
                continue;
            }

            $this->line("-> {$user->email}");
            $sent = true;

            if (! $this->option('dry-run')) {
                $message = (new IssueDigest($newIssues, $inReview, $openQuestions, $overdue, $user->name))
                    ->from(
                        $sender->mailSetting->from_address,
                        $sender->mailSetting->from_name ?: $sender->name
                    );

                Mail::mailer($smtpMailer->mailerFor($sender))
                    ->to($user)
                    ->send($message);
            }
        }

        if (! $sent) {
            $this->info('Nothing is open; no email was sent.');
        }

        return self::SUCCESS;
    }

    protected function recipients()
    {
        $userModel = config('issueboard.user_model');

        if ($explicit = config('issueboard.digest.recipients')) {
            return $userModel::whereIn('email', $explicit)->get();
        }

        return $userModel::query()
            ->when(
                Schema::hasColumn((new $userModel)->getTable(), 'role'),
                fn ($q) => $q->whereIn('role', ['admin', 'manager', 'developer'])
            )
            ->get();
    }
}
