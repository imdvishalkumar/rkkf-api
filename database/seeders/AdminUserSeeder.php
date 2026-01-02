<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@rkkf.com'],
            [
                'firstname' => 'Admin',
                'lastname' => 'User',
                'email' => 'admin@rkkf.com',
                'password' => 'admin123', // Plain text for compatibility with existing system
                'role' => 1, // Admin role
                'mobile' => null,
            ]
        );

        $this->command->info('Admin user created!');
        $this->command->info('Email: admin@rkkf.com');
        $this->command->info('Password: admin123');
    }
}

