<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\IssueBoard\Enums\IssueStatus;
use Modules\IssueBoard\Models\Issue;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = collect([
            ['name' => 'Workspace Admin', 'email' => 'admin@issueboard.test', 'role' => UserRole::Admin],
            ['name' => 'Project Manager', 'email' => 'manager@issueboard.test', 'role' => UserRole::Manager],
            ['name' => 'Development Team', 'email' => 'developer@issueboard.test', 'role' => UserRole::Developer],
            ['name' => 'Team Member', 'email' => 'member@issueboard.test', 'role' => UserRole::Member],
            ['name' => 'Read-only Viewer', 'email' => 'viewer@issueboard.test', 'role' => UserRole::Viewer],
        ])->mapWithKeys(function (array $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $data + ['password' => 'password', 'email_verified_at' => now()]
            );

            return [$data['role']->value => $user];
        });

        $projects = collect([
            ['name' => 'Website', 'description' => 'Public website improvements and content.', 'color' => '#f97316'],
            ['name' => 'Customer Portal', 'description' => 'Account and service experience.', 'color' => '#0891b2'],
            ['name' => 'Operations', 'description' => 'Internal processes and tools.', 'color' => '#7c3aed'],
        ])->map(fn (array $project) => Project::firstOrCreate(['name' => $project['name']], $project));

        $samples = [
            [
                'title' => 'Update pricing page comparison',
                'description' => 'The feature comparison is missing the newest support option. Add it so customers can compare plans without contacting the team.',
                'suggested_solution' => 'Add a new comparison row and link it to the support policy.',
                'status' => IssueStatus::New,
                'priority' => 1,
                'project_id' => $projects[0]->id,
                'assigned_to' => $users['manager']->id,
            ],
            [
                'title' => 'Confirm onboarding email wording',
                'description' => 'The welcome email needs a final decision on the setup timeline before implementation.',
                'suggested_solution' => 'Use a short three-step checklist and remove the duplicate help link.',
                'status' => IssueStatus::Review,
                'priority' => 2,
                'project_id' => $projects[1]->id,
                'assigned_to' => $users['manager']->id,
            ],
            [
                'title' => 'Improve file upload feedback',
                'description' => 'Large screenshots look inactive while uploading. Show progress and keep the submit action disabled until they finish.',
                'suggested_solution' => 'Use Livewire upload events to display per-file progress.',
                'status' => IssueStatus::InProgress,
                'priority' => 2,
                'project_id' => $projects[1]->id,
                'assigned_to' => $users['developer']->id,
            ],
            [
                'title' => 'Document weekly review owner',
                'description' => 'The weekly review responsibility is now documented in the operations handbook.',
                'status' => IssueStatus::Done,
                'priority' => 3,
                'project_id' => $projects[2]->id,
                'assigned_to' => $users['member']->id,
                'closed_at' => now(),
            ],
        ];

        foreach ($samples as $sample) {
            Issue::updateOrCreate(
                ['title' => $sample['title']],
                $sample + [
                    'created_by' => $users['member']->id,
                    'contact_name' => $users['member']->name,
                    'contact_email' => $users['member']->email,
                ]
            );
        }
    }
}
