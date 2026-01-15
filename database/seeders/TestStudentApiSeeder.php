<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestStudentApiSeeder extends Seeder
{
    /**
     * Seed test data for Student APIs.
     * Run: php artisan db:seed --class=TestStudentApiSeeder
     */
    public function run(): void
    {
        $this->command->info('Seeding test data for Student APIs...');

        // 1. Create a test coupon (unused)
        $existingCoupon = DB::table('coupon')->where('coupon_txt', 'TESTDISCOUNT50')->first();
        if (!$existingCoupon) {
            DB::table('coupon')->insert([
                'coupon_txt' => 'TESTDISCOUNT50',
                'amount' => 50,
                'used' => 0,
            ]);
            $this->command->info('Created test coupon: TESTDISCOUNT50');
        } else {
            // Reset coupon to unused
            DB::table('coupon')->where('coupon_txt', 'TESTDISCOUNT50')->update(['used' => 0]);
            $this->command->info('Reset test coupon: TESTDISCOUNT50');
        }

        // 2. Check for existing students to use for testing
        $testStudent = DB::table('students')->where('active', 1)->first();

        if ($testStudent) {
            $this->command->info("Found existing active student: {$testStudent->email} (ID: {$testStudent->student_id})");

            // Check if matching user exists
            $user = DB::table('users')->where('email', $testStudent->email)->first();
            if ($user) {
                $this->command->info("Matching user found. Can test authenticated APIs.");
            } else {
                $this->command->info("No matching user found. Creating one...");
                DB::table('users')->insert([
                    'firstname' => $testStudent->firstname ?? 'Test',
                    'lastname' => $testStudent->lastname ?? 'User',
                    'email' => $testStudent->email,
                    'password' => Hash::make('password123'),
                    'mobile' => '9999999999',
                    'role' => 'user',
                ]);
                $this->command->info("Created user with password: password123");
            }
        }

        $this->command->info('');
        $this->command->info('=== Test Data Summary ===');
        $this->command->info('Coupon Code: TESTDISCOUNT50 (discount: 50)');
        if ($testStudent) {
            $this->command->info("Test Student Email: {$testStudent->email}");
            $this->command->info('Test Password: password123 (or existing password)');
        }
        $this->command->info('');
        $this->command->info('Test APIs:');
        $this->command->info('1. POST /api/forgot-password { "email": "<student_email>" }');
        $this->command->info('2. POST /api/login { "email": "<student_email>", "password": "password123" }');
        $this->command->info('3. GET /api/students/status (with Bearer token)');
        $this->command->info('4. GET /api/students/exam-results (with Bearer token)');
        $this->command->info('5. GET /api/coupons/validate?coupon=TESTDISCOUNT50 (with Bearer token)');
        $this->command->info('6. POST /api/upload (multipart with image file)');
    }
}
