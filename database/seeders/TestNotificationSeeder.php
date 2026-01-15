<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class TestNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Run: php artisan db:seed --class=TestNotificationSeeder
     */
    public function run(): void
    {
        $this->command->info('Seeding test data for Notifications API...');

        try {
            // 1. Ensure we have a test user and student linked by email
            $email = 'test.student.notif@rkkf.org';

            // Create/Get User
            try {
                $user = DB::table('users')->where('email', $email)->first();
                if (!$user) {
                    DB::table('users')->insert([
                        'firstname' => 'Test',
                        'lastname' => 'StudentNotif',
                        'email' => $email,
                        'password' => Hash::make('password123'),
                        'mobile' => '9999999999',
                        'role' => 'user',
                    ]);
                    $this->command->info("Created test user: {$email}");
                }
            } catch (Exception $e) {
                $this->command->error("Error creating user: " . $e->getMessage());
                // Fallback: try to find any user
            }

            // Create/Get Student
            try {
                $student = DB::table('students')->where('email', $email)->first();
                if (!$student) {
                    // Need a branch
                    $branch = DB::table('branch')->first();
                    $branchId = $branch ? $branch->branch_id : 1;

                    $studentId = DB::table('students')->insertGetId([
                        'firstname' => 'Test',
                        'lastname' => 'StudentNotif',
                        'email' => $email,
                        'branch_id' => $branchId,
                        'active' => 1,
                        'belt_id' => 1
                    ]);
                    $this->command->info("Created test student with ID: {$studentId}");
                } else {
                    $studentId = $student->student_id;
                    $this->command->info("Using existing student ID: {$studentId}");
                }
            } catch (Exception $e) {
                $this->command->error("Error creating student: " . $e->getMessage());
                // Fallback: try to use ID 1
                $studentId = 1;
                $this->command->warn("Falling back to student ID 1");
            }

            // 2. Clear existing notifications for this student
            DB::table('notification')->where('student_id', $studentId)->delete();
            $this->command->info('Cleared old notifications for this student.');

            // 3. Insert Test Notifications
            $notifications = [
                [
                    'title' => 'Exam',
                    'details' => '2023 SEP 03 is on 2023-09-03',
                    'student_id' => $studentId,
                    'viewed' => 0, // Unread
                    'type' => 'Exam',
                    'sent' => 1,
                    'timestamp' => now()->subDays(2),
                ],
                [
                    'title' => 'Event',
                    'details' => '2023 Jun 18 is on 2023-09-18',
                    'student_id' => $studentId,
                    'viewed' => 1, // Read
                    'type' => 'Event',
                    'sent' => 1,
                    'timestamp' => now()->subDays(2),
                ],
                [
                    'title' => 'Fees Reminder',
                    'details' => 'Gentle remind to pay Your due fees of April before 15th APR. 2023 to avail discount. If already paid, kindly ignore this.',
                    'student_id' => $studentId,
                    'viewed' => 0, // Unread
                    'type' => 'Fees',
                    'sent' => 1,
                    'timestamp' => now()->subDays(1),
                ],
                [
                    'title' => 'Karate Championship',
                    'details' => 'Upcoming district level championship on next Sunday. Register now!',
                    'student_id' => $studentId,
                    'viewed' => 0,
                    'type' => 'Event',
                    'sent' => 1,
                    'timestamp' => now()->subHours(5),
                ],
                [
                    'title' => 'Exam Results Out',
                    'details' => 'Results for the Winter Belt Exam have been published.',
                    'student_id' => $studentId,
                    'viewed' => 1,
                    'type' => 'Exam',
                    'sent' => 1,
                    'timestamp' => now()->subWeek(),
                ],
            ];

            // Insert one by one to find the culprit
            foreach ($notifications as $n) {
                try {
                    DB::table('notification')->insert($n);
                } catch (Exception $e) {
                    $this->command->error("Error inserting notification: " . $e->getMessage());
                }
            }
            $this->command->info('Inserted test notifications.');

            $this->command->info('');
            $this->command->info('=== TEST CREDENTIALS ===');
            $this->command->info("User: {$email} / password123");
            $this->command->info('Student ID: ' . $studentId);
            $this->command->info('');
        } catch (Exception $e) {
            $this->command->error("General Error: " . $e->getMessage());
        }
    }
}
