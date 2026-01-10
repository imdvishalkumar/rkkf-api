<?php
/**
 * Verify and Insert Test Fee Records
 * 
 * Usage: php tests/verify_fee_data.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\Fee;

echo "===========================================\n";
echo "VERIFY AND INSERT TEST FEE DATA\n";
echo "===========================================\n\n";

// 1. Find user-student pair
$user = null;
$student = null;

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

if (!$user || !$student) {
    echo "ERROR: No matching user-student pair found!\n";
    exit(1);
}

echo "User: {$user->email} (ID: {$user->user_id})\n";
echo "Student: {$student->firstname} {$student->lastname} (ID: {$student->student_id})\n";
echo "Branch ID: {$student->branch_id}\n\n";

// 2. Check existing fees
$fees = Fee::where('student_id', $student->student_id)
    ->orderBy('year', 'desc')
    ->orderBy('months', 'desc')
    ->get();

echo "Current Fee Records: " . count($fees) . "\n";

if (count($fees) > 0) {
    echo "\nExisting Fee Records:\n";
    foreach ($fees as $fee) {
        echo "  - Fee ID: {$fee->fee_id}, Month: {$fee->months}, Year: {$fee->year}, Amount: {$fee->amount}, Date: {$fee->date}\n";
    }
} else {
    echo "No fee records found. Inserting test data...\n\n";
}

// 3. Insert additional test records if needed for history
if (count($fees) < 3) {
    echo "\nInserting additional test fee records for history...\n";

    $testRecords = [
        ['months' => 10, 'year' => 2025, 'amount' => 1500, 'date' => '2025-10-15'],
        ['months' => 11, 'year' => 2025, 'amount' => 1500, 'date' => '2025-11-10'],
        ['months' => 12, 'year' => 2025, 'amount' => 1500, 'date' => '2025-12-10'],
    ];

    foreach ($testRecords as $record) {
        // Check if record exists
        $exists = Fee::where('student_id', $student->student_id)
            ->where('months', $record['months'])
            ->where('year', $record['year'])
            ->exists();

        if (!$exists) {
            try {
                $fee = new Fee();
                $fee->student_id = $student->student_id;
                $fee->months = $record['months'];
                $fee->year = $record['year'];
                $fee->date = $record['date'];
                $fee->amount = $record['amount'];
                $fee->coupon_id = 0;
                $fee->additional = 0;
                $fee->disabled = 0;
                $fee->mode = 'cash';
                $fee->remarks = 'Test data';
                $fee->up = 0;
                $fee->dump = 0;
                $fee->new_remarks = '';
                $fee->save();

                echo "  Inserted: Month {$record['months']}, Year {$record['year']}\n";
            } catch (\Exception $e) {
                echo "  ERROR inserting Month {$record['months']}: {$e->getMessage()}\n";
            }
        } else {
            echo "  Skipped (exists): Month {$record['months']}, Year {$record['year']}\n";
        }
    }
}

// 4. Final count
$finalCount = Fee::where('student_id', $student->student_id)->count();
echo "\n===========================================\n";
echo "FINAL FEE RECORDS COUNT: {$finalCount}\n";

// 5. Get last paid fee for summary/due calculation
$lastFee = Fee::where('student_id', $student->student_id)
    ->orderBy('year', 'desc')
    ->orderBy('months', 'desc')
    ->first();

if ($lastFee) {
    echo "Last Paid: Month {$lastFee->months}, Year {$lastFee->year}\n";
    echo "Next Due: Month " . (($lastFee->months % 12) + 1) . ", Year " . ($lastFee->months == 12 ? $lastFee->year + 1 : $lastFee->year) . "\n";
}

echo "===========================================\n\n";

echo "API TEST INFO:\n";
echo "Email: {$user->email}\n";
echo "Password: password (if created by script) or your existing password\n\n";

echo "Test these endpoints:\n";
echo "1. GET /api/fees/due\n";
echo "2. GET /api/fees/summary\n";
echo "3. GET /api/fees/history\n";
