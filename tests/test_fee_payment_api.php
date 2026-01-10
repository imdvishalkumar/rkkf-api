<?php
/**
 * Fees Payment API Test Script
 * 
 * This script provides examples of API requests for the fee payment flow.
 * Run this file to insert test data and see example API requests.
 * 
 * Usage: php tests/test_fee_payment_api.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "FEES PAYMENT API - TEST DATA & EXAMPLES\n";
echo "===========================================\n\n";

// ============================================
// 1. CHECK EXISTING DATA
// ============================================
echo "1. CHECKING EXISTING DATA...\n";
echo "-------------------------------------------\n";

$student = DB::table('students')->first();
if (!$student) {
    echo "ERROR: No students found in database!\n";
    exit(1);
}

echo "Found Student:\n";
echo "  - Student ID: {$student->student_id}\n";
echo "  - Name: {$student->firstname} {$student->lastname}\n";
echo "  - Email: {$student->email}\n";
echo "  - Branch ID: {$student->branch_id}\n\n";

// Check if user exists with same email
$user = DB::table('users')->where('email', $student->email)->first();
if ($user) {
    echo "Found User linked to student:\n";
    echo "  - User ID: {$user->user_id}\n";
    echo "  - Email: {$user->email}\n";
} else {
    echo "WARNING: No user found with email {$student->email}\n";
    echo "Creating test user...\n";

    $userId = DB::table('users')->insertGetId([
        'firstname' => $student->firstname,
        'lastname' => $student->lastname,
        'email' => $student->email,
        'password' => password_hash('test123', PASSWORD_BCRYPT),
        'mobile' => $student->selfno ?? '9999999999',
        'role' => 'user',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created User ID: {$userId}\n";
}

// Check fees for this student
$lastFee = DB::table('fees')
    ->where('student_id', $student->student_id)
    ->orderBy('year', 'desc')
    ->orderBy('months', 'desc')
    ->first();

if ($lastFee) {
    echo "\nLast Fee Record:\n";
    echo "  - Fee ID: {$lastFee->fee_id}\n";
    echo "  - Month: {$lastFee->months}\n";
    echo "  - Year: {$lastFee->year}\n";
    echo "  - Amount: ₹{$lastFee->amount}\n";
} else {
    echo "\nNo fee records found. Creating test fee record...\n";
    $feeId = DB::table('fees')->insertGetId([
        'student_id' => $student->student_id,
        'months' => 12,
        'year' => 2025,
        'date' => '2025-12-15',
        'amount' => 1500,
        'coupon_id' => 0,
        'additional' => 0,
        'disabled' => 0,
        'mode' => 'cash',
        'remarks' => 'Test fee record',
    ]);
    echo "Created Fee ID: {$feeId}\n";
}

// ============================================
// 2. API REQUEST EXAMPLES
// ============================================
echo "\n\n";
echo "===========================================\n";
echo "2. API REQUEST EXAMPLES\n";
echo "===========================================\n\n";

$baseUrl = "http://192.168.1.6:8080/api";

echo "STEP 1: Login to get token\n";
echo "-------------------------------------------\n";
echo "POST {$baseUrl}/login\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    "email" => $student->email,
    "password" => "your_password_here"
], JSON_PRETTY_PRINT);
echo "\n\n";

echo "STEP 2: Get Due Fees\n";
echo "-------------------------------------------\n";
echo "GET {$baseUrl}/fees/due\n";
echo "Authorization: Bearer <your_token>\n";
echo "Content-Type: application/json\n\n";
echo "Expected Response:\n";
echo json_encode([
    "status" => true,
    "message" => "Due fees calculated successfully",
    "data" => [
        "last_paid" => [
            "fee_id" => 123,
            "months" => 12,
            "year" => 2025,
            "amount" => "1500.00"
        ],
        "due" => [
            [
                "feeFor" => "1 Month",
                "monthPay" => "1",
                "yearPay" => 2026,
                "amountPay" => 1800,
                "lateFee" => 0,
                "discountedFee" => 0,
                "showSpinner" => 0
            ],
            [
                "feeFor" => "2 Months",
                "monthPay" => "1,2",
                "yearPay" => 2026,
                "amountPay" => 3400,
                "lateFee" => 0,
                "discountedFee" => 0,
                "showSpinner" => 0
            ],
            [
                "feeFor" => "3 Months",
                "monthPay" => "1,2,3",
                "yearPay" => 2026,
                "amountPay" => 4500,
                "lateFee" => 0,
                "discountedFee" => 100,
                "showSpinner" => 0
            ]
        ]
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

echo "STEP 3: Initiate Payment\n";
echo "-------------------------------------------\n";
echo "POST {$baseUrl}/fees/payment/initiate\n";
echo "Authorization: Bearer <your_token>\n";
echo "Content-Type: application/json\n\n";
echo "Request Body:\n";
echo json_encode([
    "months" => "1,2,3",
    "year" => 2026,
    "amount" => 4500,
    "coupon_id" => 0
], JSON_PRETTY_PRINT);
echo "\n\n";
echo "Expected Response:\n";
echo json_encode([
    "status" => true,
    "message" => "Payment order created successfully",
    "data" => [
        "success" => true,
        "orderId" => "order_xxxxxxxxxx",
        "keyId" => "rzp_live_xxxxxx",
        "amount" => 4500,
        "currency" => "INR",
        "transaction_id" => 123
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

echo "STEP 4: Verify Payment (After Razorpay Checkout)\n";
echo "-------------------------------------------\n";
echo "POST {$baseUrl}/fees/payment/verify\n";
echo "Authorization: Bearer <your_token>\n";
echo "Content-Type: application/json\n\n";
echo "Request Body:\n";
echo json_encode([
    "razorpay_order_id" => "order_xxxxxxxxxx",
    "razorpay_payment_id" => "pay_xxxxxxxxxx",
    "razorpay_signature" => "signature_hash_here"
], JSON_PRETTY_PRINT);
echo "\n\n";
echo "Expected Response:\n";
echo json_encode([
    "status" => true,
    "message" => "Payment verified successfully",
    "data" => [
        "success" => true,
        "message" => "Payment verified successfully",
        "transaction_id" => 123
    ]
], JSON_PRETTY_PRINT);
echo "\n\n";

// ============================================
// 3. CURL COMMANDS
// ============================================
echo "===========================================\n";
echo "3. CURL COMMANDS (Copy & Paste)\n";
echo "===========================================\n\n";

echo "# Login\n";
echo "curl -X POST {$baseUrl}/login \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"email\":\"{$student->email}\",\"password\":\"your_password\"}'\n\n";

echo "# Get Due Fees\n";
echo "curl -X GET {$baseUrl}/fees/due \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN_HERE\" \\\n";
echo "  -H \"Content-Type: application/json\"\n\n";

echo "# Initiate Payment\n";
echo "curl -X POST {$baseUrl}/fees/payment/initiate \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN_HERE\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"months\":\"1,2,3\",\"year\":2026,\"amount\":4500,\"coupon_id\":0}'\n\n";

echo "# Verify Payment\n";
echo "curl -X POST {$baseUrl}/fees/payment/verify \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN_HERE\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"razorpay_order_id\":\"order_xxx\",\"razorpay_payment_id\":\"pay_xxx\",\"razorpay_signature\":\"sig_xxx\"}'\n\n";

echo "===========================================\n";
echo "TEST DATA SETUP COMPLETE!\n";
echo "===========================================\n";
