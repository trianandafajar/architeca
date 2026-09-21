<?php

namespace Database\Seeders;

use App\Models\BudgetItem;
use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\ProgressUpdate;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectDataSeeder extends Seeder
{
    public function run(): void
    {
        $contractor = User::where('email', 'contractor@gmail.com')->first();
        $staff = User::where('email', 'staff@gmail.com')->first();

        if (!$contractor || !$staff) return;

        $project = Project::updateOrCreate(
            ['name' => 'Commercial Office Tower Construction'],
            [
                'owner_id' => $contractor->id,
                'client_name' => 'Global Ventures Corp',
                'location' => '742 Evergreen Terrace, New York, NY',
                'contract_value' => 160000.00,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
                'status' => 'active',
            ]
        );

        ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $contractor->id],
            ['role' => 'owner']
        );
        ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $staff->id],
            ['role' => 'worker']
        );

        ProjectTask::updateOrCreate(
            ['project_id' => $project->id, 'title' => 'Foundation & Structural Works'],
            ['percentage_weight' => 50, 'is_completed' => true, 'assigned_to' => $contractor->id]
        );

        BudgetItem::updateOrCreate(
            ['project_id' => $project->id, 'item_name' => 'Foundation & Structural Works'],
            [
                'quantity' => 1,
                'unit' => 'Lot',
                'unit_price' => 50000.00,
                'total_price' => 50000.00,
                'notes' => 'Reinforced deep concrete foundations',
            ]
        );

        ProgressUpdate::updateOrCreate(
            ['project_id' => $project->id, 'progress_date' => now()->subMonth()->format('Y-m-d')],
            [
                'user_id' => $contractor->id,
                'percentage' => 25.00,
                'notes' => 'Excavation and foundation concrete works successfully completed.',
            ]
        );

        DailyReport::updateOrCreate(
            ['project_id' => $project->id, 'report_date' => now()->subDays(2)->format('Y-m-d')],
            [
                'user_id' => $staff->id,
                'workers_count' => 15,
                'work_description' => 'Second-floor slab concrete pouring executed smoothly.',
                'progress_percentage' => 5.00,
            ]
        );

        $expenseSeeds = [
            ['date' => now()->subMonths(4)->startOfMonth()->addDays(3), 'desc' => 'Rebar and cement batch 1', 'amt' => 2900.00],
            ['date' => now()->subMonths(4)->startOfMonth()->addDays(12), 'desc' => 'Weekly labor wages - Week 1', 'amt' => 1150.00],
        ];

        foreach ($expenseSeeds as $data) {
            Expense::updateOrCreate(
                ['project_id' => $project->id, 'description' => $data['desc']],
                [
                    'user_id' => $contractor->id,
                    'expense_date' => $data['date'],
                    'category' => 'material',
                    'amount' => $data['amt'],
                ]
            );
        }
    }
}
