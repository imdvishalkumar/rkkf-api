<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test events and attendance records for testing current/past event filtering.
     */
    public function run(): void
    {
        $today = Carbon::now();

        // Hardcoded student ID and user ID for testing
        $studentId = 1002;
        $userId = 3;

        // Get or create a category
        $category = DB::table('categories')->first();
        $categoryId = $category?->id ?? 1;

        $testEvents = [
            // PAST EVENTS (to_date < today)
            [
                'name' => 'Annual Championship 2025',
                'type' => 'Tournament',
                'from_date' => $today->copy()->subDays(30)->format('Y-m-d'),
                'to_date' => $today->copy()->subDays(28)->format('Y-m-d'),
                'venue' => 'Mumbai Sports Complex',
                'description' => 'Past tournament event for testing',
                'fees' => 500.00,
                'penalty' => 100.00,
                'fees_due_date' => $today->copy()->subDays(35)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->subDays(32)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],
            [
                'name' => 'Winter Camp 2025',
                'type' => 'Camp-Activity',
                'from_date' => $today->copy()->subDays(15)->format('Y-m-d'),
                'to_date' => $today->copy()->subDays(13)->format('Y-m-d'),
                'venue' => 'Ahmedabad',
                'description' => 'Past winter camp event',
                'fees' => 800.00,
                'penalty' => 150.00,
                'fees_due_date' => $today->copy()->subDays(20)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->subDays(17)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],
            [
                'name' => 'Grading Ceremony',
                'type' => 'Ceremony',
                'from_date' => $today->copy()->subDays(5)->format('Y-m-d'),
                'to_date' => $today->copy()->subDays(5)->format('Y-m-d'),
                'venue' => 'Delhi Center',
                'description' => 'Recent past grading ceremony',
                'fees' => 300.00,
                'penalty' => 50.00,
                'fees_due_date' => $today->copy()->subDays(10)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->subDays(7)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],

            // CURRENT/ONGOING EVENTS (from_date <= today AND to_date >= today)
            [
                'name' => 'National Training Week',
                'type' => 'Training',
                'from_date' => $today->copy()->subDays(2)->format('Y-m-d'),
                'to_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'venue' => 'Pune Training Center',
                'description' => 'Currently ongoing training event',
                'fees' => 1000.00,
                'penalty' => 200.00,
                'fees_due_date' => $today->copy()->subDays(7)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->subDays(4)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],

            // UPCOMING EVENTS (from_date > today)
            [
                'name' => 'Spring Championship 2026',
                'type' => 'Tournament',
                'from_date' => $today->copy()->addDays(10)->format('Y-m-d'),
                'to_date' => $today->copy()->addDays(12)->format('Y-m-d'),
                'venue' => 'Bangalore Sports Arena',
                'description' => 'Upcoming spring tournament',
                'fees' => 600.00,
                'penalty' => 100.00,
                'fees_due_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->addDays(8)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],
            [
                'name' => 'Summer Picnic 2026',
                'type' => 'Picnic',
                'from_date' => $today->copy()->addDays(15)->format('Y-m-d'),
                'to_date' => $today->copy()->addDays(15)->format('Y-m-d'),
                'venue' => 'Lonavala',
                'description' => 'Annual summer picnic outing',
                'fees' => 400.00,
                'penalty' => 75.00,
                'fees_due_date' => $today->copy()->addDays(10)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->addDays(13)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],
            [
                'name' => 'Advanced Workshop',
                'type' => 'Workshop',
                'from_date' => $today->copy()->addDays(20)->format('Y-m-d'),
                'to_date' => $today->copy()->addDays(21)->format('Y-m-d'),
                'venue' => 'Hyderabad Convention Center',
                'description' => 'Advanced techniques workshop',
                'fees' => 750.00,
                'penalty' => 125.00,
                'fees_due_date' => $today->copy()->addDays(15)->format('Y-m-d'),
                'penalty_due_date' => $today->copy()->addDays(18)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryId,
            ],
        ];

        $this->command->info('Creating test events...');

        foreach ($testEvents as $eventData) {
            // Insert event
            $eventId = DB::table('event')->insertGetId($eventData);

            $this->command->info("Created event: {$eventData['name']} (ID: {$eventId})");

            // Create attendance record for student (to mark as participated)
            if ($studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 1,
                    'user_id' => $userId,
                ]);
                $this->command->info("  -> Added attendance for student ID: {$studentId}");
            }
        }

        $this->command->info('');
        $this->command->info('Test events created successfully!');
        $this->command->info('Past events: 3, Current/Ongoing: 1, Upcoming: 3');
        if ($studentId) {
            $this->command->info("Attendance records created for student ID: {$studentId}");
        }
    }
}
