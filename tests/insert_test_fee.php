<?php
/**
 * Insert Test Fee Record
 * 
 * Usage: php tests/insert_test_fee.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\Fee;

echo "===========================================\n";
echo "INSERT TEST DATA FOR FEES SUMMARY\n";
echo "===========================================\n\n";

// 1. Find a user that has a matching student
$user = null;
$student = null;

// Try to find existing pair
$users = User::all();
foreach ($users as $u) {
    if (!$u->email)
        continue;
    $s = Student::where('email', $u->email)->first();
    if ($s) {
        $user = $u;
        $student = $s;
        break;
    }
}

// If no pair found, create one
if (!$user || !$student) {
    echo "No matching user-student pair found.\n";

    // Find any student
    $student = Student::first();
    if (!$student) {
        echo "ERROR: No students in database!\n";
        exit(1);
    }

    echo "Found student: {$student->firstname} {$student->lastname} ({$student->email})\n";

    // Check if user exists for this student
    $user = User::where('email', $student->email)->first();

    if (!$user) {
        echo "Creating user for this student...\n";
        try {
            // Use DB query to avoid model validation issues if any
            $userId = DB::table('users')->insertGetId([
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'email' => $student->email,
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'mobile' => $student->selfno ?? '9999999999',
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user = User::find($userId);
            echo "Created User ID: {$userId}\n";
        } catch (\Exception $e) {
            echo "Failed to create user: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

echo "Using User: {$user->email} (ID: {$user->user_id})\n";
echo "Linked Student: {$student->firstname} {$student->lastname} (ID: {$student->student_id})\n";

// 3. Check existing fees
$count = Fee::where('student_id', $student->student_id)->count();
echo "Existing Fee Records: {$count}\n";

if ($count > 0) {
    echo "Student already has fee records.\n";
    $lastFee = Fee::where('student_id', $student->student_id)
        ->orderBy('year', 'desc')
        ->orderBy('months', 'desc')
        ->first();
    echo "Last Paid: Month {$lastFee->months}, Year {$lastFee->year}\n";
} else {
    // 4. Insert a test fee record (Paid up to Dec 2025)
    echo "Inserting test fee record (Paid for Dec 2025)...\n";

    try {
        $fee = new Fee();
        $fee->student_id = $student->student_id;
        $fee->months = 12; // Paid up to December
        $fee->year = 2025;
        $fee->date = now()->subMonth()->format('Y-m-d'); // Paid last month
        $fee->amount = 1500.00;
        $fee->coupon_id = 0;
        $fee->additional = 0;
        $fee->disabled = 0;
        $fee->mode = 'cash';
        $fee->remarks = 'Initial test data';
        $fee->up = 0;
        $fee->dump = 0;
        $fee->new_remarks = '';
        $fee->save();

        echo "SUCCESS: Fee record inserted! (ID: {$fee->fee_id})\n";
    } catch (\Exception $e) {
        $errorMsg = "ERROR INSERTING FEE: " . $e->getMessage() . "\n" . $e->getTraceAsString();
        file_put_contents('tests/error_log.txt', $errorMsg);
        echo "ERROR: Exception occurred. Check tests/error_log.txt for details.\n";
    }
}

echo "\n-------------------------------------------\n";
echo "API TEST CREDENTIALS:\n";
echo "Email: {$user->email}\n";
echo "Password: password (if newly created) or existing password\n";
echo "-------------------------------------------\n";
