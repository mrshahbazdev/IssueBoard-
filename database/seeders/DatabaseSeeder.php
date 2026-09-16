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
        $admin = User::updateOrCreate(
            ['email' => 'admin@issueboard.test'],
            [
                'name' => 'Workspace Admin',
                'role' => UserRole::Admin,
                'invited_by' => null,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $projects = collect([
            ['name' => 'Website', 'description' => 'Public website improvements and content.', 'color' => '#f97316', 'team_owner_id' => $admin->id],
            ['name' => 'Customer Portal', 'description' => 'Account and service experience.', 'color' => '#0891b2', 'team_owner_id' => $admin->id],
            ['name' => 'Operations', 'description' => 'Internal processes and tools.', 'color' => '#7c3aed', 'team_owner_id' => $admin->id],
        ])->map(fn (array $project) => Project::firstOrCreate(['name' => $project['name']], $project));

        $samples = [
            [
                'title' => 'Update pricing page comparison',
                'description' => 'The feature comparison is missing the newest support option. Add it so customers can compare plans without contacting the team.',
                'suggested_solution' => 'Add a new comparison row and link it to the support policy.',
                'status' => IssueStatus::New,
                'priority' => 1,
                'project_id' => $projects[0]->id,
                'team_owner_id' => $admin->id,
                'assigned_to' => null,
            ],
            [
                'title' => 'Confirm onboarding email wording',
                'description' => 'The welcome email needs a final decision on the setup timeline before implementation.',
                'suggested_solution' => 'Use a short three-step checklist and remove the duplicate help link.',
                'status' => IssueStatus::Review,
                'priority' => 2,
                'project_id' => $projects[1]->id,
                'team_owner_id' => $admin->id,
                'assigned_to' => null,
            ],
            [
                'title' => 'Improve file upload feedback',
                'description' => 'Large screenshots look inactive while uploading. Show progress and keep the submit action disabled until they finish.',
                'suggested_solution' => 'Use Livewire upload events to display per-file progress.',
                'status' => IssueStatus::InProgress,
                'priority' => 2,
                'project_id' => $projects[1]->id,
                'team_owner_id' => $admin->id,
                'assigned_to' => null,
            ],
            [
                'title' => 'Document weekly review owner',
                'description' => 'The weekly review responsibility is now documented in the operations handbook.',
                'status' => IssueStatus::Done,
                'priority' => 3,
                'project_id' => $projects[2]->id,
                'team_owner_id' => $admin->id,
                'assigned_to' => null,
                'closed_at' => now(),
            ],
        ];

        foreach ($samples as $sample) {
            Issue::updateOrCreate(
                ['title' => $sample['title']],
                $sample + [
                    'created_by' => $admin->id,
                    'contact_name' => $admin->name,
                    'contact_email' => $admin->email,
                ]
            );
        }
    }
}
