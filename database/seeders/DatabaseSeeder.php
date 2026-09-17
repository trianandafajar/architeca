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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Expense::truncate();
        DailyReport::truncate();
        ProgressUpdate::truncate();
        BudgetItem::truncate();
        ProjectMember::truncate();
        Project::truncate();
        User::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call(ShieldSeeder::class);

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $admin->syncRoles(['admin']);

        $contractor = User::create([
            'name' => 'Lead Contractor',
            'email' => 'contractor@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'contractor',
        ]);
        $contractor->syncRoles(['contractor']);

        $staff = User::create([
            'name' => 'Field Supervisor',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
        $staff->syncRoles(['staff']);

        $project = Project::create([
            'name' => 'Commercial Office Tower Construction',
            'owner_id' => $contractor->id,
            'client_name' => 'Global Ventures Corp',
            'location' => '742 Evergreen Terrace, New York, NY',
            'contract_value' => 160000.00,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
        ]);

        ProjectMember::create([
            'project_id' => $project->id, 
            'user_id' => $contractor->id,
            'role' => 'owner'
        ]);

        ProjectMember::create([
            'project_id' => $project->id, 
            'user_id' => $staff->id,
            'role' => 'worker'
        ]);

        BudgetItem::create([
            'project_id' => $project->id,
            'item_name' => 'Foundation & Structural Works',
            'quantity' => 1,
            'unit' => 'Lot',
            'unit_price' => 50000.00,
            'total_price' => 50000.00,
            'notes' => 'Reinforced deep concrete foundations',
        ]);

        BudgetItem::create([
            'project_id' => $project->id,
            'item_name' => 'Masonry & Wall Plastering',
            'quantity' => 500,
            'unit' => 'm2',
            'unit_price' => 22.00,
            'total_price' => 11000.00,
            'notes' => 'Standard brick masonry and premium plaster finish',
        ]);

        ProgressUpdate::create([
            'project_id' => $project->id,
            'user_id' => $contractor->id,
            'progress_date' => now()->subMonth(),
            'percentage' => 25.00,
            'notes' => 'Excavation and foundation concrete works successfully completed.',
        ]);

        ProgressUpdate::create([
            'project_id' => $project->id,
            'user_id' => $contractor->id,
            'progress_date' => now()->subDays(10),
            'percentage' => 45.00,
            'notes' => 'First-floor structural columns installation completed.',
        ]);

        DailyReport::create([
            'project_id' => $project->id,
            'user_id' => $staff->id,
            'report_date' => now()->subDays(2),
            'workers_count' => 15,
            'work_description' => 'Second-floor slab concrete pouring executed smoothly.',
            'issues' => 'Minor weather delay of 1 hour due to morning precipitation.',
            'progress_percentage' => 5.00,
        ]);

        $expenseSeeds = [
            ['expense_date' => now()->subMonths(4)->startOfMonth()->addDays(3), 'category' => 'material', 'description' => 'Rebar and cement batch 1', 'amount' => 2900.00],
            ['expense_date' => now()->subMonths(4)->startOfMonth()->addDays(12), 'category' => 'labor', 'description' => 'Weekly labor wages - Week 1', 'amount' => 1150.00],
            ['expense_date' => now()->subMonths(3)->startOfMonth()->addDays(2), 'category' => 'material', 'description' => 'Red bricks and river sand supply', 'amount' => 2400.00],
            ['expense_date' => now()->subMonths(3)->startOfMonth()->addDays(15), 'category' => 'equipment', 'description' => 'Concrete mixer machine rental', 'amount' => 800.00],
            ['expense_date' => now()->subMonths(3)->startOfMonth()->addDays(20), 'category' => 'transport', 'description' => 'Excavated soil debris haulage', 'amount' => 440.00],
            ['expense_date' => now()->subMonths(2)->startOfMonth()->addDays(5), 'category' => 'labor', 'description' => 'Weekly labor wages - Week 5', 'amount' => 1380.00],
            ['expense_date' => now()->subMonths(2)->startOfMonth()->addDays(18), 'category' => 'material', 'description' => 'Additional cement bags (150 units)', 'amount' => 1470.00],
            ['expense_date' => now()->subMonths(1)->startOfMonth()->addDays(5), 'category' => 'equipment', 'description' => 'Scaffolding framework rental', 'amount' => 990.00],
            ['expense_date' => now()->startOfMonth()->addDays(5), 'category' => 'material', 'description' => 'Ceramic tiles and sanitary fixtures', 'amount' => 2160.00],
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
