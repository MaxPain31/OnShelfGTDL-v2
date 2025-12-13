<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin role
        $adminRole = Role::where('name', 'Administrator')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found. Please run RoleSeeder first.');
            return;
        }

        DB::transaction(function () use ($adminRole) {
            // Create or update admin user
            $user = User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'role_id' => $adminRole->id,
                    'password' => Hash::make('123asd'),
                    'deactivated' => false,
                    'email_verified_at' => now(),
                ]
            );
        });
    }
}


