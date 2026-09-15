<?php

use App\Models\Project;
use App\Models\User;
use Modules\IssueBoard\Enums\IssueStatus;

return [
    'user_model' => User::class,
    'project_model' => Project::class,
    /*
    | Uploads
    */
    'disk' => env('ISSUEBOARD_DISK', 'public'),
    'directory' => 'issues',
    'max_upload_kb' => 8192,
    'accepted_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'csv', 'xlsx', 'docx'],

    /*
    | Which destination statuses each role may set.
    */
    'transitions' => [
        'viewer' => [],
        'member' => [IssueStatus::New->value, IssueStatus::Review->value],
        'developer' => [IssueStatus::Review->value, IssueStatus::InProgress->value, IssueStatus::Done->value],
        'manager' => ['*'],
        'admin' => ['*'],
    ],

    /*
    | Optional callback that resolves a role from the user model.
    */
    'role_resolver' => null,

    /*
    | Weekly overview email
    */
    'digest' => [
        'enabled' => true,
        'recipients' => [],
        'day' => 'monday',
        'time' => '08:00',
    ],

    'route' => [
        'prefix' => 'board',
        'middleware' => ['web', 'auth'],
    ],
];
