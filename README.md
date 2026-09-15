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
- Admin team invitations, pending invites, resend/cancel actions, and role management
- Personal SMTP settings managed from each profile, with encrypted passwords
- Private issue access for the assigned user, Admins, and Managers

## Requirements

- PHP 8.2+
- Composer
- SQLite or MySQL
- PHP extensions required by Laravel, including DOM, Mbstring, OpenSSL, PDO, Tokenizer, and XML
- Apache `mod_rewrite` when deploying with `.htaccess`

## Local setup

```bash
git clone https://github.com/mrshahbazdev/IssueBoard-.git
cd IssueBoard-
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
PHP_CLI_SERVER_WORKERS=4 php artisan serve --host=0.0.0.0 --port=8000 --no-reload
```

Open `http://127.0.0.1:8000`.

## Apache and shared-hosting deployment

The safest setup is to point the domain document root to the repository's `public/` directory. Laravel's standard front-controller rules are already provided in `public/.htaccess`.

If the hosting panel cannot change the document root, point the domain to the repository root instead. The included root `.htaccess` forwards every request into `public/` without exposing Laravel application files. This fallback requires Apache `mod_rewrite` and permission to use `.htaccess` rules (`AllowOverride All`).

For the first deployment, run these commands from the repository root:

```bash
cp .env.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

On later deployments, keep the existing `.env` and `APP_KEY`; do not generate a new key. Run `composer install --no-dev --optimize-autoloader`, migrations, and `php artisan optimize` after updating the code.

Before serving traffic, update `.env` for production:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
```

Configure the `DB_*` values for the production database. For SQLite, create `database/database.sqlite`; for MySQL, set the host, port, database, username, and password supplied by the hosting provider.

The web-server user must be able to write to:

```text
storage/
bootstrap/cache/
```

The production frontend assets are committed under `public/build`, so deployment does not require Node.js or `npm run build`. Node.js 22+ is only needed when changing frontend source files:

```bash
npm install
npm run build
```

Commit the refreshed `public/build` output with frontend changes.

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
| Admin | Full workspace access, invitations, and team-role management |
| Manager | See all issues and manage priorities, assignments, deletion, and every workflow stage |
| Developer | Work on assigned issues and move them through review, progress, and done |
| Team member | Work on assigned issues, discuss, and submit them for review |
| Viewer | Read-only access to assigned issues and their conversations |

Regular users only see issues assigned to them. Admins and Managers can see all issues.

Public registration creates a Team member account. Admins can invite users with a selected role, resend or cancel pending invitations, and change existing roles.

## Personal SMTP

Every user can configure an SMTP account from **Profile and security**. The SMTP password is encrypted using Laravel's application key and is never shown again. No `MAIL_*` values are required in `.env` for invitations or user-triggered notifications.

An Admin must save SMTP settings before sending an invitation. Issue notifications use the SMTP account of the user who created the update. The weekly digest uses the first configured Admin or Manager SMTP account.

## Useful commands

```bash
composer test
composer exec pint -- --test
npm run build
php artisan issueboard:digest --dry-run
```

The weekly digest is scheduled for Monday at 08:00 by default. Configure an Admin or Manager SMTP account in the application and enable the scheduler before using it in production.

For the Laravel scheduler, add this cron entry on the server:

```cron
* * * * * cd /path/to/IssueBoard- && php artisan schedule:run >> /dev/null 2>&1
```
