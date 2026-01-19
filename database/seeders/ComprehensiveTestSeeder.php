<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComprehensiveTestSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $this->command->info('Starting Comprehensive Test Data Seeder...');
            $this->command->newLine();

            $this->truncateTables();

            $this->seedBelts();
            $this->seedBranches();
            $this->seedCategories();
            $this->seedProductCategories();
            $this->seedUsers();
            $this->seedStudents();
            $this->seedProducts();
            $this->seedEvents();
            $this->seedExams();
            $this->seedAttendance();
            $this->seedEventFees();
            $this->seedExamFees();
            $this->seedFees();
            $this->seedLeaves();
            $this->seedNotifications();
            $this->seedCart();
            $this->seedOrders();
            $this->seedCoupons();

            $this->command->newLine();
            $this->command->info('Comprehensive test data seeded successfully!');
            $this->printTestCredentials();
        } catch (\Exception $e) {
            $this->command->error("SEEDER FAILED: " . $e->getMessage());
            $this->command->error("File: " . basename($e->getFile()) . " Line: " . $e->getLine());
        }
    }

    private function truncateTables(): void
    {
        $this->command->info('Truncating all tables...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = [
            'personal_access_tokens',
            'cart',
            'orders',
            'fees',
            'exam_fees',
            'exam_attendance',
            'event_fees',
            'event_attendance',
            'event_likes',
            'event_comments',
            'attendance',
            'leave_table',
            'notification',
            'students',
            'users',
            'exam',
            'special_case_exam',
            'event',
            'products',
            'product_categories',
            'categories',
            'belt',
            'branch',
            'coupon',
            'order_details',
            'reviews',
            'transcation',
        ];
        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
            } catch (\Exception $e) {
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('   Tables truncated.');
    }

    private function seedBelts(): void
    {
        $this->command->info('Seeding belts...');
        $belts = [
            ['belt_id' => 1, 'name' => 'White Belt', 'code' => 1, 'priority' => 1, 'exam_fees' => 300],
            ['belt_id' => 2, 'name' => 'Yellow Belt', 'code' => 2, 'priority' => 2, 'exam_fees' => 400],
            ['belt_id' => 3, 'name' => 'Orange Belt', 'code' => 3, 'priority' => 3, 'exam_fees' => 500],
            ['belt_id' => 4, 'name' => 'Green Belt', 'code' => 4, 'priority' => 4, 'exam_fees' => 600],
            ['belt_id' => 5, 'name' => 'Blue Belt', 'code' => 5, 'priority' => 5, 'exam_fees' => 700],
            ['belt_id' => 6, 'name' => 'Brown Belt', 'code' => 6, 'priority' => 6, 'exam_fees' => 800],
            ['belt_id' => 7, 'name' => 'Black Belt', 'code' => 7, 'priority' => 7, 'exam_fees' => 1500],
        ];
        foreach ($belts as $belt)
            DB::table('belt')->insert($belt);
    }

    private function seedBranches(): void
    {
        $this->command->info('Seeding branches...');
        $branches = [
            [
                'branch_id' => 1,
                'name' => 'Main Dojo - Ahmedabad',
                'days' => 'Mon,Tue,Wed,Thu,Fri,Sat',
                'fees' => 500,
                'late' => 50,
                'discount' => 10,
                'address' => '203, Shree Kashi Parekh Complex',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'zip_code' => '380009',
                'phone' => '9999999999',
                'email' => 'main@rkkf.org',
                'is_active' => 1,
            ],
            [
                'branch_id' => 2,
                'name' => 'City Center Branch',
                'days' => 'Mon,Wed,Fri',
                'fees' => 600,
                'late' => 50,
                'discount' => 10,
                'address' => '45, Commerce House, CG Road',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'zip_code' => '380006',
                'phone' => '9888888888',
                'email' => 'citycenter@rkkf.org',
                'is_active' => 1,
            ],
            [
                'branch_id' => 3,
                'name' => 'Satellite Branch',
                'days' => 'Tue,Thu,Sat',
                'fees' => 550,
                'late' => 50,
                'discount' => 10,
                'address' => '12, Iscon Mall, Satellite',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'zip_code' => '380015',
                'phone' => '9777777777',
                'email' => 'satellite@rkkf.org',
                'is_active' => 1,
            ]
        ];
        foreach ($branches as $branch)
            DB::table('branch')->insert($branch);
    }

    private function seedCategories(): void
    {
        $this->command->info('Seeding event categories...');
        $categories = [
            ['name' => 'Tournaments', 'description' => 'Competitive tournaments', 'active' => 1],
            ['name' => 'Workshops', 'description' => 'Training workshops', 'active' => 1],
            ['name' => 'Exhibitions', 'description' => 'Martial arts exhibitions', 'active' => 1],
            ['name' => 'Training Camps', 'description' => 'Intensive training camps', 'active' => 1],
            ['name' => 'Picnic', 'description' => 'Fun outdoor activities', 'active' => 1],
        ];
        foreach ($categories as $c) {
            $c['created_at'] = now()->format('Y-m-d H:i:s');
            $c['updated_at'] = now()->format('Y-m-d H:i:s');
            DB::table('categories')->insert($c);
        }
    }

    private function seedProductCategories(): void
    {
        $this->command->info('Seeding product categories...');
        $categories = [['name' => 'Uniforms', 'active' => 1], ['name' => 'Equipment', 'active' => 1], ['name' => 'Accessories', 'active' => 1], ['name' => 'Books', 'active' => 1]];
        foreach ($categories as $c) {
            $c['created_at'] = now()->format('Y-m-d H:i:s');
            $c['updated_at'] = now()->format('Y-m-d H:i:s');
            DB::table('product_categories')->insert($c);
        }
    }

    private function seedUsers(): void
    {
        $this->command->info('Seeding users (1 admin, 2 users, 2 instructors)...');
        $users = [
            ['firstname' => 'Super', 'lastname' => 'Admin', 'email' => 'admin@rkkf.org', 'mobile' => '9999999999', 'role' => 'admin'],
            ['firstname' => 'Rahul', 'lastname' => 'Sharma', 'email' => 'rahul.sharma@test.com', 'mobile' => '9876543210', 'role' => 'user'],
            ['firstname' => 'Priya', 'lastname' => 'Patel', 'email' => 'priya.patel@test.com', 'mobile' => '9876543211', 'role' => 'user'],
            ['firstname' => 'Sensei', 'lastname' => 'Kumar', 'email' => 'sensei.kumar@rkkf.org', 'mobile' => '9888888888', 'role' => 'instructor'],
            ['firstname' => 'Master', 'lastname' => 'Singh', 'email' => 'master.singh@rkkf.org', 'mobile' => '9888888889', 'role' => 'instructor'],
        ];
        foreach ($users as $u) {
            $u['password'] = Hash::make('password123');
            $u['created_at'] = now()->format('Y-m-d H:i:s');
            $u['updated_at'] = now()->format('Y-m-d H:i:s');
            DB::table('users')->insert($u);
        }
    }

    private function seedStudents(): void
    {
        $this->command->info('Seeding students...');

        $students = [
            [
                'student_id' => 1001,
                'firstname' => 'Rahul',
                'lastname' => 'Sharma',
                'gender' => 1,
                'email' => 'rahul.sharma@test.com',
                'password' => Hash::make('password123'),
                'belt_id' => 3,
                'selfno' => '9876543210',
                'branch_id' => 1,
                'address' => '123, Green Park, Ahmedabad',
                'pincode' => '380001',
                'dob' => '2010-05-15',
                'doj' => '2024-01-15',
                'active' => 1,
                'profile_img' => 'default.png',
                'std' => '10th',
                'call_flag' => 0,
                'dadno' => '',
                'dadwp' => '',
                'momno' => '',
                'momwp' => '',
                'selfwp' => '',
            ],
            [
                'student_id' => 1002,
                'firstname' => 'Priya',
                'lastname' => 'Patel',
                'gender' => 2,
                'email' => 'priya.patel@test.com',
                'password' => Hash::make('password123'),
                'belt_id' => 2,
                'selfno' => '9876543211',
                'branch_id' => 1,
                'address' => '456, Blue Avenue, Ahmedabad',
                'pincode' => '380002',
                'dob' => '2012-08-20',
                'doj' => '2024-03-01',
                'active' => 1,
                'profile_img' => 'default.png',
                'std' => '8th',
                'call_flag' => 0,
                'dadno' => '',
                'dadwp' => '',
                'momno' => '',
                'momwp' => '',
                'selfwp' => '',
            ],
            [
                'student_id' => 1003,
                'firstname' => 'Amit',
                'lastname' => 'Desai',
                'gender' => 1,
                'email' => 'amit.desai@test.com',
                'password' => Hash::make('password123'),
                'belt_id' => 5,
                'selfno' => '9876543212',
                'branch_id' => 2,
                'address' => '789, Red Colony, Ahmedabad',
                'pincode' => '380003',
                'dob' => '2008-02-10',
                'doj' => '2023-06-01',
                'active' => 1,
                'profile_img' => 'default.png',
                'std' => '12th',
                'call_flag' => 0,
                'dadno' => '',
                'dadwp' => '',
                'momno' => '',
                'momwp' => '',
                'selfwp' => '',
            ],
            [
                'student_id' => 1004,
                'firstname' => 'Sneha',
                'lastname' => 'Joshi',
                'gender' => 2,
                'email' => 'sneha.joshi@test.com',
                'password' => Hash::make('password123'),
                'belt_id' => 1,
                'selfno' => '9876543213',
                'branch_id' => 3,
                'address' => '321, Yellow Street, Ahmedabad',
                'pincode' => '380004',
                'dob' => '2011-11-25',
                'doj' => '2024-02-15',
                'active' => 1,
                'profile_img' => 'default.png',
                'std' => '9th',
                'call_flag' => 0,
                'dadno' => '',
                'dadwp' => '',
                'momno' => '',
                'momwp' => '',
                'selfwp' => '',
            ],
            [
                'student_id' => 1005,
                'firstname' => 'Inactive',
                'lastname' => 'Student',
                'gender' => 1,
                'email' => 'inactive@test.com',
                'password' => Hash::make('password123'),
                'belt_id' => 4,
                'selfno' => '9876543214',
                'branch_id' => 1,
                'address' => '555, Grey Lane, Ahmedabad',
                'pincode' => '380005',
                'dob' => '2009-07-30',
                'doj' => '2022-01-01',
                'active' => 0,
                'profile_img' => 'default.png',
                'std' => '11th',
                'call_flag' => 0,
                'dadno' => '',
                'dadwp' => '',
                'momno' => '',
                'momwp' => '',
                'selfwp' => '',
            ]
        ];

        foreach ($students as $index => $student) {
            try {
                DB::table('students')->insert($student);
            } catch (\Exception $e) {
                $this->command->error("STUDENT FAIL ID {$student['student_id']}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    private function seedProducts(): void
    {
        $this->command->info('Seeding products...');
        $products = [
            ['product_category_id' => 1, 'name' => 'Karate Gi (Uniform)', 'details' => 'High quality cotton karate uniform.', 'image1' => 'gi.jpg', 'belt_ids' => '1,2,3,4,5,6,7', 'is_active' => 1],
            ['product_category_id' => 2, 'name' => 'Training Pads Set', 'details' => 'Complete training pads set.', 'image1' => 'pads.jpg', 'belt_ids' => '0', 'is_active' => 1],
            ['product_category_id' => 3, 'name' => 'Sports Water Bottle', 'details' => 'Durable 1L sports water bottle.', 'image1' => 'bottle.jpg', 'belt_ids' => '0', 'is_active' => 1],
            ['product_category_id' => 4, 'name' => 'Karate Training Manual', 'details' => 'Comprehensive training manual.', 'image1' => 'book.jpg', 'belt_ids' => '1,2,3', 'is_active' => 1],
            ['product_category_id' => 3, 'name' => 'Gym Bag', 'details' => 'Spacious gym bag with compartments.', 'image1' => 'bag.jpg', 'belt_ids' => '0', 'is_active' => 1],
        ];
        foreach ($products as $p) {
            $p['created_at'] = now()->format('Y-m-d H:i:s');
            $p['updated_at'] = now()->format('Y-m-d H:i:s');
            try {
                DB::table('products')->insert($p);
            } catch (\Exception $e) {
                $this->command->error("Failed to insert product: " . $p['name'] . " - " . $e->getMessage());
                throw $e;
            }
        }
    }

    private function seedEvents(): void
    {
        $this->command->info('Seeding events...');
        $now = Carbon::now();
        $events = [
            [
                'name' => 'Regional Championship 2026',
                'subtitle' => 'Annual Championship',
                'from_date' => $now->copy()->addDays(30)->format('Y-m-d'),
                'to_date' => $now->copy()->addDays(32)->format('Y-m-d'),
                'venue' => 'Sports Complex, Ahmedabad',
                'type' => 'Competition',
                'description' => 'Annual regional karate championship.',
                'fees' => 500,
                'fees_due_date' => $now->copy()->addDays(20)->format('Y-m-d'),
                'penalty' => 100,
                'penalty_due_date' => $now->copy()->addDays(25)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => 1,
                'likes' => 15,
                'comments' => 5,
                'shares' => 3,
            ],
            [
                'name' => 'Weekend Bootcamp',
                'subtitle' => 'Intensive Training',
                'from_date' => $now->copy()->addDays(10)->format('Y-m-d'),
                'to_date' => $now->copy()->addDays(11)->format('Y-m-d'),
                'venue' => 'Main Dojo, Ahmedabad',
                'type' => 'Bootcamp',
                'description' => 'Weekend bootcamp focusing on conditioning.',
                'fees' => 400,
                'fees_due_date' => $now->copy()->addDays(5)->format('Y-m-d'),
                'penalty' => 50,
                'penalty_due_date' => $now->copy()->addDays(7)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => 4,
                'likes' => 8,
                'comments' => 2,
                'shares' => 1,
            ],
            [
                'name' => 'Annual Picnic 2025',
                'subtitle' => 'Fun Day Out',
                'from_date' => $now->copy()->subDays(30)->format('Y-m-d'),
                'to_date' => $now->copy()->subDays(30)->format('Y-m-d'),
                'venue' => 'Adventure Park, Ahmedabad',
                'type' => 'Picnic',
                'description' => 'Annual picnic for all students.',
                'fees' => 300,
                'fees_due_date' => $now->copy()->subDays(40)->format('Y-m-d'),
                'penalty' => 50,
                'penalty_due_date' => $now->copy()->subDays(35)->format('Y-m-d'),
                'isPublished' => 1,
                'category_id' => 5,
                'likes' => 45,
                'comments' => 12,
                'shares' => 8,
            ]
        ];
        foreach ($events as $e) {
            try {
                DB::table('event')->insert($e);
            } catch (\Exception $ex) {
                $this->command->error("EVENT INSERT FAIL: " . $ex->getMessage());
            }
        }
    }

    private function seedExams(): void
    {
        $this->command->info('Seeding exams...');
        $now = Carbon::now();
        $exams = [
            [
                'exam_id' => 1,
                'name' => 'White Belt Exam',
                'date' => $now->copy()->addDays(45)->format('Y-m-d'),
                'sessions_count' => 20,
                'fees' => 300,
                'fess_due_date' => $now->copy()->addDays(35)->format('Y-m-d'),
                'location' => 'Main Dojo, Ahmedabad',
                'from_criteria' => $now->copy()->subMonths(3)->format('Y-m-d'),
                'to_criteria' => $now->copy()->format('Y-m-d'),
                'isPublished' => 1,
            ],
            [
                'exam_id' => 2,
                'name' => 'Yellow Belt Exam',
                'date' => $now->copy()->addDays(50)->format('Y-m-d'),
                'sessions_count' => 25,
                'fees' => 400,
                'fess_due_date' => $now->copy()->addDays(40)->format('Y-m-d'),
                'location' => 'Main Dojo, Ahmedabad',
                'from_criteria' => $now->copy()->subMonths(4)->format('Y-m-d'),
                'to_criteria' => $now->copy()->format('Y-m-d'),
                'isPublished' => 1,
            ],
            [
                'exam_id' => 3,
                'name' => 'Orange Belt Exam',
                'date' => $now->copy()->subDays(30)->format('Y-m-d'),
                'sessions_count' => 30,
                'fees' => 500,
                'fess_due_date' => $now->copy()->subDays(40)->format('Y-m-d'),
                'location' => 'Main Dojo, Ahmedabad',
                'from_criteria' => $now->copy()->subMonths(6)->format('Y-m-d'),
                'to_criteria' => $now->copy()->subDays(35)->format('Y-m-d'),
                'isPublished' => 1,
            ]
        ];
        foreach ($exams as $e)
            DB::table('exam')->insert($e);
    }

    private function seedAttendance(): void
    {
        $this->command->info('Seeding attendance...');
        $students = [1001, 1002, 1003, 1004];
        $now = Carbon::now();
        foreach ($students as $studentId) {
            $branchId = $studentId == 1003 ? 2 : ($studentId == 1004 ? 3 : 1);
            for ($i = 0; $i < 60; $i++) {
                $date = $now->copy()->subDays($i);
                if ($date->isWeekend())
                    continue;
                $attend = rand(1, 100) <= 80 ? 'P' : 'A';
                DB::table('attendance')->insert(['student_id' => $studentId, 'branch_id' => $branchId, 'date' => $date->format('Y-m-d'), 'attend' => $attend, 'user_id' => 4, 'is_additional' => 0]);
            }
        }
    }

    private function seedEventFees(): void
    {
        $this->command->info('Seeding event fees...');
        $base = ['mode' => 'online', 'rp_order_id' => 'order_test_event', 'status' => 1, 'amount' => 500];
        DB::table('event_fees')->insert([['event_id' => 1, 'student_id' => 1001, 'date' => now()->format('Y-m-d')] + $base]);
        DB::table('event_fees')->insert([['event_id' => 1, 'student_id' => 1002, 'date' => now()->format('Y-m-d')] + $base]);
        DB::table('event_fees')->insert([['event_id' => 3, 'student_id' => 1001, 'date' => now()->subDays(35)->format('Y-m-d')] + $base]);
        DB::table('event_attendance')->insert(['event_id' => 3, 'student_id' => 1001, 'attend' => 'P', 'user_id' => 4]);
    }

    private function seedExamFees(): void
    {
        $this->command->info('Seeding exam fees...');
        $base = ['mode' => 'online', 'rp_order_id' => 'order_test_exam', 'status' => 1, 'up' => 0, 'dump' => 0];
        DB::table('exam_fees')->insert([['exam_id' => 3, 'student_id' => 1001, 'exam_belt_id' => 3, 'date' => now()->subDays(35)->format('Y-m-d'), 'amount' => 500] + $base]);
        DB::table('exam_attendance')->insert(['exam_id' => 3, 'student_id' => 1001, 'attend' => 'P', 'certificate_no' => '2025120001', 'user_id' => 4]);
    }

    private function seedFees(): void
    {
        $this->command->info('Seeding monthly fees...');
        $students = [1001, 1002, 1003, 1004];
        $now = Carbon::now();
        foreach ($students as $studentId) {
            for ($i = 0; $i < 6; $i++) {
                $date = $now->copy()->subMonths($i);
                if ($studentId == 1002 && $i < 2)
                    continue;
                DB::table('fees')->insert([
                    'student_id' => $studentId,
                    'months' => (int) $date->format('m'),
                    'year' => (int) $date->format('Y'),
                    'amount' => 500,
                    'date' => $date->format('Y-m-d'),
                    'coupon_id' => 0,
                    'additional' => 0,
                    'disabled' => 0,
                    'mode' => 'cash',
                    'remarks' => 'Monthly fee payment',
                    'up' => 0,
                    'dump' => 0,
                    'new_remarks' => '',
                    'call_flag' => 0,
                ]);
            }
        }
    }

    private function seedLeaves(): void
    {
        $this->command->info('Seeding leaves...');
        $leaves = [
            ['student_id' => 1001, 'from_date' => now()->addDays(5)->format('Y-m-d'), 'to_date' => now()->addDays(7)->format('Y-m-d'), 'reason' => 'Family function', 'status' => 0, 'applied_at' => now()->format('Y-m-d H:i:s')],
            ['student_id' => 1002, 'from_date' => now()->subDays(10)->format('Y-m-d'), 'to_date' => now()->subDays(8)->format('Y-m-d'), 'reason' => 'Medical leave', 'status' => 1, 'applied_at' => now()->subDays(15)->format('Y-m-d H:i:s')],
        ];
        foreach ($leaves as $leave)
            DB::table('leave_table')->insert($leave);
    }

    private function seedNotifications(): void
    {
        $this->command->info('Seeding notifications...');
        $notifications = [
            ['title' => 'Welcome to RKKF!', 'details' => 'Welcome to Rajkot Karate Federation!', 'student_id' => 1001, 'viewed' => 0, 'type' => 'general', 'sent' => 1, 'timestamp' => now()->format('Y-m-d H:i:s')],
            ['title' => 'Upcoming Championship', 'details' => 'Regional Championship registrations open!', 'student_id' => 1001, 'viewed' => 0, 'type' => 'event', 'sent' => 1, 'timestamp' => now()->subDays(2)->format('Y-m-d H:i:s')],
        ];
        foreach ($notifications as $n)
            DB::table('notification')->insert($n);
    }

    private function seedCart(): void
    {
        $this->command->info('Seeding cart...');
        DB::table('cart')->insert(['student_id' => 1001, 'product_id' => 1, 'qty' => 1, 'variation_id' => 1]);
        DB::table('cart')->insert(['student_id' => 1001, 'product_id' => 3, 'qty' => 2, 'variation_id' => 0]);
    }

    private function seedOrders(): void
    {
        $this->command->info('Seeding orders...');
        DB::table('orders')->insert([
            'student_id' => 1001,
            'product_id' => 1,
            'qty' => 1,
            'p_price' => 1200,
            'status' => 1,
            'date' => now()->subDays(15)->format('Y-m-d'),
            'flag_delivered' => 1,
            'viewed' => 1,
            'counter' => 1,
            'rp_order_id' => 'order_legacy_1',
            'name_var' => 'Size M',
            'variation_id' => 1,
            'flag' => 1,
        ]);
    }

    private function seedCoupons(): void
    {
        $this->command->info('Seeding coupons...');
        $coupons = [['coupon_txt' => 'WELCOME10', 'amount' => 10, 'used' => 0], ['coupon_txt' => 'FLAT100', 'amount' => 100, 'used' => 0]];
        foreach ($coupons as $c)
            DB::table('coupon')->insert($c);
    }

    private function printTestCredentials(): void
    {
        $this->command->newLine();
        $this->command->info('================================================');
        $this->command->info('              TEST CREDENTIALS                  ');
        $this->command->info('================================================');
        $this->command->newLine();
        $this->command->info('ADMIN:');
        $this->command->line('   Email: admin@rkkf.org | Password: password123');
        $this->command->newLine();
        $this->command->info('USERS (Students):');
        $this->command->line('   1. Email: rahul.sharma@test.com | Password: password123 | Student ID: 1001');
        $this->command->line('   2. Email: priya.patel@test.com | Password: password123 | Student ID: 1002');
        $this->command->newLine();
        $this->command->info('INSTRUCTORS:');
        $this->command->line('   1. Email: sensei.kumar@rkkf.org | Password: password123');
        $this->command->line('   2. Email: master.singh@rkkf.org | Password: password123');
        $this->command->newLine();
        $this->command->info('COUPONS: WELCOME10, FLAT100');
        $this->command->info('================================================');
    }
}
