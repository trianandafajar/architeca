<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Lead Contractor',
                'email' => 'contractor@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'contractor',
            ],
            [
                'name' => 'Field Supervisor',
                'email' => 'staff@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $user->syncRoles([$userData['role']]);
        }
    }
}
