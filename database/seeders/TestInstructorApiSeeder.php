<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestInstructorApiSeeder extends Seeder
{
    /**
     * Seed test data for Instructor APIs.
     * Run: php artisan db:seed --class=TestInstructorApiSeeder
     */
    public function run(): void
    {
        $this->command->info('Seeding test data for Instructor APIs...');

        // 1. Create test instructor if not exists
        $instructorEmail = 'test.instructor@rkkf.org';
        $instructor = DB::table('users')->where('email', $instructorEmail)->first();

        if (!$instructor) {
            try {
                DB::table('users')->insert([
                    'firstname' => 'Test',
                    'lastname' => 'Instructor',
                    'email' => $instructorEmail,
                    'password' => Hash::make('password123'),
                    'mobile' => '8888888888',
                    'role' => 'instructor',
                ]);
                $this->command->info("Created instructor: {$instructorEmail}");
            } catch (\Exception $e) {
                $this->command->error("Failed to create instructor: " . $e->getMessage());
            }
        } else {
            $this->command->info("Instructor exists: {$instructorEmail}");
        }

        // 2. Create test USER (for negative testing - should NOT access instructor APIs)
        $userEmail = 'test.user@rkkf.org';
        $testUser = DB::table('users')->where('email', $userEmail)->first();

        if (!$testUser) {
            try {
                DB::table('users')->insert([
                    'firstname' => 'Test',
                    'lastname' => 'User',
                    'email' => $userEmail,
                    'password' => Hash::make('password123'),
                    'mobile' => '7777777777',
                    'role' => 'user',
                ]);
                $this->command->info("Created test user: {$userEmail}");
            } catch (\Exception $e) {
                $this->command->error("Failed to create user: " . $e->getMessage());
            }
        } else {
            $this->command->info("Test user exists: {$userEmail}");
        }

        // 3. Get existing data for reference
        $branch = DB::table('branch')->first();
        $branchId = $branch ? $branch->branch_id : 1;

        // Update branch days
        if ($branch) {
            DB::table('branch')
                ->where('branch_id', $branchId)
                ->update(['days' => 'Mon,Tue,Wed,Thu,Fri']);
        }

        $student = DB::table('students')->where('active', 1)->first();
        $studentId = $student ? $student->student_id : 'N/A (create a student first)';

        $event = DB::table('event')->orderBy('event_id', 'desc')->first();
        $eventId = $event ? $event->event_id : 'N/A';

        $exam = DB::table('exam')->orderBy('exam_id', 'desc')->first();
        $examId = $exam ? $exam->exam_id : 'N/A';

        // Clear event/exam attendance for fresh testing if they exist
        if ($event) {
            DB::table('event_attendance')->where('event_id', $event->event_id)->delete();
            $this->command->info("Cleared event_attendance for event {$event->event_id}");
        }
        if ($exam) {
            DB::table('exam_attendance')->where('exam_id', $exam->exam_id)->delete();
            $this->command->info("Cleared exam_attendance for exam {$exam->exam_id}");
        }

        $today = now()->format('Y-m-d');

        $this->command->info('');
        $this->command->info('===============================================');
        $this->command->info('=== TEST DATA SUMMARY ===');
        $this->command->info('===============================================');
        $this->command->info("Branch ID: {$branchId}");
        $this->command->info("Student ID: {$studentId}");
        $this->command->info("Event ID: {$eventId}");
        $this->command->info("Exam ID: {$examId}");
        $this->command->info('');
        $this->command->info('=== TEST CREDENTIALS ===');
        $this->command->info("Instructor: {$instructorEmail} / password123");
        $this->command->info("User (for rejection test): {$userEmail} / password123");
        $this->command->info('');
        $this->command->info('=== API TEST PAYLOADS ===');
        $this->command->info('');

        if (is_numeric($studentId)) {
            $this->command->info('POST /api/instructor/attendance/count');
            $this->command->info('{"student_id": ' . $studentId . ', "date": "' . $today . '", "branch_id": ' . $branchId . '}');
            $this->command->info('');
            $this->command->info('POST /api/instructor/fastrack/attendance');
            $this->command->info('{"student_id": ' . $studentId . ', "hours": 2.5, "branch_id": ' . $branchId . '}');
            $this->command->info('');
        }

        if (is_numeric($eventId) && is_numeric($studentId)) {
            $this->command->info('POST /api/instructor/events/attendance');
            $this->command->info('{"event_id": ' . $eventId . ', "attendanceArray": "[{\"present_student_id\":' . $studentId . '}]"}');
            $this->command->info('');
        }

        if (is_numeric($examId) && is_numeric($studentId)) {
            $this->command->info('POST /api/instructor/exams/attendance');
            $this->command->info('{"exam_id": ' . $examId . ', "attendanceArray": "[{\"present_student_id\":' . $studentId . '}]"}');
            $this->command->info('');
        }

        $this->command->info('===============================================');
    }
}
