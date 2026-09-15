# IssueBoard

A standalone internal communication board for project changes, small issues, questions, and decisions.

## What it includes

- Four-stage workflow: red **New**, yellow **In review**, blue **In progress**, green **Done**
- Project and assignee filters
- Priorities and due dates
- Descriptions and suggested solutions
- Links, screenshots, and file attachments
- Reporter contact details
- Comments, follow-up questions, threaded replies, and answered state
- Rich text for descriptions, suggested solutions, comments, and replies
- English and German interface with a persistent language switch
- Status history and weekly digest command
- Custom authentication, profile, and password screens
- Role-based access with Admin, Manager, Developer, Team member, and Viewer roles
- Admin team-role management

## Requirements

- PHP 8.2+
- Composer
- SQLite for the default local setup

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8000 --no-reload
```

Open `http://127.0.0.1:8000`.

Production frontend assets are included in Git. Node.js 22+ is only required when changing and rebuilding the frontend:

```bash
npm install
npm run build
```

## Local demo accounts

All seeded accounts use the password `password`.

| Role | Email |
|---|---|
| Admin | `admin@issueboard.test` |
| Manager | `manager@issueboard.test` |
| Developer | `developer@issueboard.test` |
| Team member | `member@issueboard.test` |
| Viewer | `viewer@issueboard.test` |

These credentials are intended only for local development.

## Role access

| Role | Access |
|---|---|
| Admin | Full access and team-role management |
| Manager | Manage tasks, priorities, assignments, deletion, and every workflow stage |
| Developer | Create, update, discuss, and move work through review, progress, and done |
| Team member | Report work, update own reports, discuss, and submit items for review |
| Viewer | Read-only board and conversation access |

Public registration creates a Team member account. Only an Admin can change roles.

## Useful commands

```bash
composer test
composer exec pint -- --test
npm run build
php artisan issueboard:digest --dry-run
```

The weekly digest is scheduled for Monday at 08:00 by default. Configure mail, queues, and the scheduler before enabling delivery in production.
