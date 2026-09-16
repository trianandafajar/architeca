<?php

namespace Database\Seeders;

use App\Models\BudgetItem;
use App\Models\DailyReport;
use App\Models\Expense;
use App\Models\ProgressUpdate;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Shield roles must exist before they are assigned to the demo users.
        $this->call(ShieldSeeder::class);

        // 1. Create Demo Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@architeca.test'],
            [
                'name' => 'Admin Architeca',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $admin->syncRoles(['admin']);

        $contractor = User::firstOrCreate(
            ['email' => 'contractor@architeca.test'],
            [
                'name' => 'Main Contractor',
                'password' => Hash::make('password'),
                'role' => 'contractor',
            ]
        );
        $contractor->syncRoles(['contractor']);

        $staff = User::firstOrCreate(
            ['email' => 'staff@architeca.test'],
            [
                'name' => 'Field Staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );
        $staff->syncRoles(['staff']);

        // 2. Create Sample Project
        $project = Project::firstOrCreate(
            ['name' => '3-Story Office Building Construction'],
            [
                'owner_id' => $contractor->id,
                'client_name' => 'PT Maju Bersama',
                'location' => 'Jl. Sudirman No. 123, Jakarta',
                'contract_value' => 2500000000.00,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(4),
                'status' => 'active',
            ]
        );

        // 3. Project Members
        ProjectMember::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $contractor->id],
            ['role' => 'owner']
        );

        ProjectMember::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => $staff->id],
            ['role' => 'worker']
        );

        // 4. Budget Items (RAB)
        if ($project->budgetItems()->count() === 0) {
            BudgetItem::create([
                'project_id' => $project->id,
                'item_name' => 'Foundation & Structure Work',
                'quantity' => 1,
                'unit' => 'Lot',
                'unit_price' => 800000000,
                'total_price' => 800000000,
                'notes' => 'Reinforced concrete foundations',
            ]);

            BudgetItem::create([
                'project_id' => $project->id,
                'item_name' => 'Wall & Plastering Work',
                'quantity' => 500,
                'unit' => 'm2',
                'unit_price' => 350000,
                'total_price' => 175000000,
                'notes' => 'Brick masonry and plaster',
            ]);
        }

        // 5. Progress Updates
        if ($project->progressUpdates()->count() === 0) {
            ProgressUpdate::create([
                'project_id' => $project->id,
                'user_id' => $contractor->id,
                'progress_date' => now()->subMonth(),
                'percentage' => 25.00,
                'notes' => 'Excavation and foundation concrete completed',
            ]);

            ProgressUpdate::create([
                'project_id' => $project->id,
                'user_id' => $contractor->id,
                'progress_date' => now()->subDays(10),
                'percentage' => 45.00,
                'notes' => 'First floor column installation completed',
            ]);
        }

        // 6. Daily Reports
        if ($project->dailyReports()->count() === 0) {
            DailyReport::create([
                'project_id' => $project->id,
                'user_id' => $staff->id,
                'report_date' => now()->subDays(2),
                'workers_count' => 15,
                'work_description' => 'Second floor slab concrete pouring went smoothly.',
                'issues' => 'Cloudy weather delayed the concrete pouring by 1 hour.',
                'progress_percentage' => 5.00,
            ]);
        }

        // 7. Expenses
        $project->expenses()->delete();
        $expenseSeeds = [
            ['expense_date' => now()->subMonths(4)->startOfMonth()->addDays(3), 'category' => 'material', 'description' => 'Rebar & cement batch 1', 'amount' => 45000000],
            ['expense_date' => now()->subMonths(4)->startOfMonth()->addDays(12), 'category' => 'labor', 'description' => 'Mason wages week 1', 'amount' => 18200000],
            ['expense_date' => now()->subMonths(3)->startOfMonth()->addDays(2), 'category' => 'material', 'description' => 'Red bricks & sand', 'amount' => 37500000],
            ['expense_date' => now()->subMonths(3)->startOfMonth()->addDays(15), 'category' => 'equipment', 'description' => 'Concrete mixer rental', 'amount' => 12500000],
            ['expense_date' => now()->subMonths(3)->startOfMonth()->addDays(20), 'category' => 'transport', 'description' => 'Excavated material haulage', 'amount' => 6800000],
            ['expense_date' => now()->subMonths(2)->startOfMonth()->addDays(5), 'category' => 'labor', 'description' => 'Mason wages week 5', 'amount' => 21400000],
            ['expense_date' => now()->subMonths(2)->startOfMonth()->addDays(18), 'category' => 'material', 'description' => 'Additional 150 bags of cement', 'amount' => 22800000],
            ['expense_date' => now()->subMonths(1)->startOfMonth()->addDays(5), 'category' => 'equipment', 'description' => 'Scaffolding rental', 'amount' => 15400000],
            ['expense_date' => now()->startOfMonth()->addDays(5), 'category' => 'material', 'description' => 'Tiles & sanitary ware', 'amount' => 33500000],
        ];

        foreach ($expenseSeeds as $data) {
            Expense::create([
                'project_id' => $project->id,
                'user_id' => $contractor->id,
                ...$data,
            ]);
        }
    }
}
