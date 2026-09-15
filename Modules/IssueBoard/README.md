# IssueBoard domain module

This directory contains the board's Livewire components, models, policies, migrations, notifications, mail, translations, and views.

The standalone application registers `IssueBoardServiceProvider` in `bootstrap/providers.php` and autoloads the module namespace from the root `composer.json`.

Application-specific users and projects live in `app/Models`. Workflow access is configured in `config/issueboard.php`.
