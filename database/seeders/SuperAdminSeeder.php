<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create the Admin User
        $adminEmail = 'admin@gmail.com';
        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'firstname' => 'Super',
                'lastname' => 'Admin',
                'password' => 'admin123', // Plain text for compatibility
                'role' => UserRole::ADMIN->value,
                'mobile' => '1234567890',
            ]
        );

        $this->command->info("Admin created: $adminEmail / admin123");
    }
}
