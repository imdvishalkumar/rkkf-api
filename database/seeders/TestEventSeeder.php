<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure categories exist
        $categories = [
            ['name' => 'Tournaments', 'description' => 'Competitive tournaments and championships'],
            ['name' => 'Workshops', 'description' => 'Training workshops and seminars'],
            ['name' => 'Exhibitions', 'description' => 'Martial arts exhibitions and demonstrations'],
            ['name' => 'Training Camps', 'description' => 'Intensive training camps'],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $existing = DB::table('categories')->where('name', $category['name'])->first();
            if (!$existing) {
                $id = DB::table('categories')->insertGetId([
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $categoryIds[$category['name']] = $id;
            } else {
                $categoryIds[$category['name']] = $existing->id;
            }
        }

        // Create test events for each category
        $events = [
            // Tournaments
            [
                'name' => 'Regional Karate Championship 2026',
                'subtitle' => 'Join us for the biggest karate tournament of the year',
                'from_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(32)->format('Y-m-d'),
                'venue' => 'Sports Complex',
                'type' => 'Competition',
                'description' => 'Annual regional karate championship featuring multiple belt levels and age groups. Open to all registered students.',
                'fees' => 500,
                'fees_due_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'penalty' => 100,
                'penalty_due_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Tournaments'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],
            [
                'name' => 'Belt Promotion Tournament',
                'subtitle' => 'Test your skills and advance to the next level',
                'from_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
                'venue' => 'Main Dojo',
                'type' => 'Belt Test',
                'description' => 'Special tournament for belt promotion. Students can demonstrate their skills and advance to higher belts.',
                'fees' => 300,
                'fees_due_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
                'penalty' => 50,
                'penalty_due_date' => Carbon::now()->addDays(40)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Tournaments'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],

            // Workshops
            [
                'name' => 'Advanced Kata Workshop',
                'subtitle' => 'Master the art of kata with expert instructors',
                'from_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(16)->format('Y-m-d'),
                'venue' => 'Training Hall A',
                'type' => 'Workshop',
                'description' => 'Intensive 2-day workshop focusing on advanced kata techniques. Perfect for students preparing for higher belt exams.',
                'fees' => 200,
                'fees_due_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'penalty' => 30,
                'penalty_due_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Workshops'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],
            [
                'name' => 'Self-Defense Techniques Seminar',
                'subtitle' => 'Learn practical self-defense skills',
                'from_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'venue' => 'Training Hall B',
                'type' => 'Seminar',
                'description' => 'One-day seminar covering essential self-defense techniques. Open to all students and parents.',
                'fees' => 150,
                'fees_due_date' => Carbon::now()->addDays(50)->format('Y-m-d'),
                'penalty' => 25,
                'penalty_due_date' => Carbon::now()->addDays(55)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Workshops'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],

            // Exhibitions
            [
                'name' => 'Martial Arts Demonstration Day',
                'subtitle' => 'Watch amazing demonstrations by our instructors',
                'from_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'venue' => 'Main Arena',
                'type' => 'Exhibition',
                'description' => 'Public exhibition showcasing various martial arts techniques. Free entry for all. Great for families!',
                'fees' => 0,
                'fees_due_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'penalty' => 0,
                'penalty_due_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Exhibitions'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],
            [
                'name' => 'Black Belt Showcase',
                'subtitle' => 'Witness the skills of our black belt students',
                'from_date' => Carbon::now()->addDays(75)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(75)->format('Y-m-d'),
                'venue' => 'Main Dojo',
                'type' => 'Exhibition',
                'description' => 'Special exhibition featuring our black belt students demonstrating advanced techniques and forms.',
                'fees' => 0,
                'fees_due_date' => Carbon::now()->addDays(75)->format('Y-m-d'),
                'penalty' => 0,
                'penalty_due_date' => Carbon::now()->addDays(75)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Exhibitions'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],

            // Training Camps
            [
                'name' => 'Summer Intensive Training Camp',
                'subtitle' => '5 days of intensive training',
                'from_date' => Carbon::now()->addDays(90)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(94)->format('Y-m-d'),
                'venue' => 'Training Camp Grounds',
                'type' => 'Training Camp',
                'description' => '5-day intensive training camp with multiple sessions daily. Includes accommodation and meals. Perfect for serious students.',
                'fees' => 2000,
                'fees_due_date' => Carbon::now()->addDays(70)->format('Y-m-d'),
                'penalty' => 200,
                'penalty_due_date' => Carbon::now()->addDays(80)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Training Camps'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],
            [
                'name' => 'Weekend Training Bootcamp',
                'subtitle' => 'Intensive weekend training session',
                'from_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(11)->format('Y-m-d'),
                'venue' => 'Training Hall A & B',
                'type' => 'Bootcamp',
                'description' => 'Weekend bootcamp focusing on conditioning, technique refinement, and sparring. Open to all levels.',
                'fees' => 400,
                'fees_due_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'penalty' => 50,
                'penalty_due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => $categoryIds['Training Camps'],
                'image' => null,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
            ],
        ];

        foreach ($events as $event) {
            DB::table('event')->insert($event);
        }

        $this->command->info('Test events created successfully!');
        $this->command->info('Created ' . count($events) . ' events across ' . count($categories) . ' categories.');
    }
}
