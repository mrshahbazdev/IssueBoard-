<?php

namespace Modules\IssueBoard\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class IssueDigest extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Collection $newIssues,
        public Collection $inReview,
        public Collection $openQuestions,
        public Collection $overdue,
        public string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('issueboard::issueboard.mail.digest_subject', [
                'open' => $this->newIssues->count() + $this->inReview->count(),
            ])
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'issueboard::mail.digest');
    }
}
