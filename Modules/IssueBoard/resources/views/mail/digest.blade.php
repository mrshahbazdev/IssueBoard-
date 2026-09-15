<x-mail::message>
# {{ __('issueboard::issueboard.mail.digest_heading') }}

{{ __('issueboard::issueboard.mail.greeting', ['name' => $recipientName]) }}

@if ($newIssues->isNotEmpty())
## {{ __('issueboard::issueboard.status.new') }} ({{ $newIssues->count() }})
@foreach ($newIssues as $issue)
- [{{ $issue->title }}]({{ route('issueboard.show', $issue) }}) — {{ $issue->project?->name ?? __('issueboard::issueboard.all_projects') }}
@endforeach
@endif

@if ($inReview->isNotEmpty())
## {{ __('issueboard::issueboard.status.review') }} ({{ $inReview->count() }})
@foreach ($inReview as $issue)
- [{{ $issue->title }}]({{ route('issueboard.show', $issue) }})
@endforeach
@endif

@if ($openQuestions->isNotEmpty())
## {{ __('issueboard::issueboard.open_question') }} ({{ $openQuestions->count() }})
@foreach ($openQuestions as $question)
- {{ $question->user->name }}: [{{ $question->issue->title }}]({{ route('issueboard.show', $question->issue) }})
@endforeach
@endif

@if ($overdue->isNotEmpty())
## {{ __('issueboard::issueboard.overdue') }} ({{ $overdue->count() }})
@foreach ($overdue as $issue)
- [{{ $issue->title }}]({{ route('issueboard.show', $issue) }}) — {{ $issue->due_date->format('d.m.Y') }}
@endforeach
@endif

<x-mail::button :url="route('issueboard.index')">
{{ __('issueboard::issueboard.mail.open_board') }}
</x-mail::button>
</x-mail::message>
