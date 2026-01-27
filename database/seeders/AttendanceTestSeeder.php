<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class AttendanceTestSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'student_test@example.com';

        // 1. Create User
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'firstname' => 'Test',
                'lastname' => 'Student',
                'mobile' => '9876543210',
                'password' => Hash::make('password'),
                'role' => 'user', // Assuming 'user' is the role for students
            ]
        );

        // 2. Create Student
        $student = Student::updateOrCreate(
            ['email' => $email],
            [
                'firstname' => 'Test',
                'lastname' => 'Student',
                'dob' => '2000-01-01',
                'doj' => '2024-01-01',
                'gender' => 1,
                'address' => 'Test Address',
                'pincode' => '123456',
                'profile_img' => 'default.png',
                'password' => Hash::make('password'),
                'branch_id' => 1,
                'belt_id' => 1,
                'active' => 1
            ]
        );

        // 3. Clear existing attendance for this student
        DB::table('attendance')->where('student_id', $student->student_id)->delete();

        // 4. Insert 12 Records (8 Present, 4 Absent)
        // Let's backdate them over the last 12 days
        $data = [];
        $startDate = Carbon::now()->subDays(12);

        // 8 Present
        for ($i = 0; $i < 8; $i++) {
            $data[] = [
                'student_id' => $student->student_id,
                'branch_id' => 1,
                'date' => $startDate->copy()->addDays($i)->format('Y-m-d'),
                'attend' => 'P',
                'user_id' => 1, // Instructor ID
                'is_additional' => 0
            ];
        }

        // 4 Absent
        for ($i = 8; $i < 12; $i++) {
            $data[] = [
                'student_id' => $student->student_id,
                'branch_id' => 1,
                'date' => $startDate->copy()->addDays($i)->format('Y-m-d'),
                'attend' => 'A',
                'user_id' => 1, // Instructor ID
                'is_additional' => 0
            ];
        }

        DB::table('attendance')->insert($data);

        $this->command->info("Test data created for email: $email");
        $this->command->info("User ID: " . $user->id);
        $this->command->info("Student ID: " . $student->student_id);
    }
}
