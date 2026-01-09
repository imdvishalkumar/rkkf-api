<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$colors = [
    'green' => "\033[32m",
    'red' => "\033[31m",
    'reset' => "\033[0m",
];

function test($name, $callback)
{
    global $colors;
    echo "Testing $name... ";
    try {
        $result = $callback();
        if ($result) {
            echo "{$colors['green']}PASS{$colors['reset']}\n";
        } else {
            echo "{$colors['red']}FAIL{$colors['reset']}\n";
        }
    } catch (Throwable $e) {
        echo "{$colors['red']}ERROR: " . $e->getMessage() . "{$colors['reset']}\n";
    }
}

// 1. Get Users
$admin = App\Models\User::where('role', 'admin')->first();
$user = App\Models\User::where('role', 'user')->first();
$instructor = App\Models\User::where('role', 'instructor')->first();

if (!$admin || !$user || !$instructor) {
    die("Missing users. Admin: " . ($admin ? 'OK' : 'MISS') . ", User: " . ($user ? 'OK' : 'MISS') . ", Inst: " . ($instructor ? 'OK' : 'MISS') . "\n");
}

echo "Using Users: Admin({$admin->user_id}), User({$user->user_id}), Instructor({$instructor->user_id})\n";

// 2. Generate Tokens
$adminToken = $admin->createToken('test-admin')->plainTextToken;
$userToken = $user->createToken('test-user')->plainTextToken;
$instructorToken = $instructor->createToken('test-instructor')->plainTextToken;

// 3. Ensure Event Exists
$event = App\Models\Event::first();
if (!$event) {
    die("No events found. Please create an event first.\n");
}
$eventId = $event->event_id;
echo "Using Event ID: $eventId\n";

$baseUrl = "http://192.168.1.6:8080/api";

function req($method, $path, $token, $data = [])
{
    global $baseUrl;
    $url = "$baseUrl/$path";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    // Disable SSL verify for local dev
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo "CURL Error: $err\n";
    }

    return ['code' => $code, 'body' => json_decode($response, true), 'raw' => $response];
}

// 4. Run Tests

// Test 1: User Comment
test("User can post comment", function () use ($eventId, $userToken) {
    $res = req('POST', "events/$eventId/comments", $userToken, ['comment' => 'User Test Comment ' . time()]);
    if ($res['code'] !== 201)
        print_r($res);
    return $res['code'] === 201;
});

// Test 2: Admin Blocked
test("Admin BLOCKED from posting comment", function () use ($eventId, $adminToken) {
    $res = req('POST', "events/$eventId/comments", $adminToken, ['comment' => 'Admin Fail Comment']);
    if ($res['code'] !== 403)
        echo "Code was " . $res['code'];
    return $res['code'] === 403;
});

// Test 3: Instructor Reply
test("Instructor can post reply", function () use ($eventId, $instructorToken, $userToken) {
    $comments = req('GET', "events/$eventId/comments", $userToken);
    $parentId = $comments['body']['data'][0]['id'] ?? null;

    if (!$parentId)
        return false;

    $res = req('POST', "events/$eventId/comments", $instructorToken, ['comment' => 'Inst Reply', 'parent_id' => $parentId]);
    if ($res['code'] !== 201)
        print_r($res);
    return $res['code'] === 201;
});

// Test 4: Like
test("User can like comment", function () use ($userToken, $eventId) {
    $comments = req('GET', "events/$eventId/comments", $userToken);
    $commentId = $comments['body']['data'][0]['id'] ?? null;
    if (!$commentId)
        return false;

    $res = req('POST', "comments/$commentId/like", $userToken);
    if ($res['code'] !== 200)
        print_r($res);

    // Toggle back if needed, or just check format
    return $res['code'] === 200 && isset($res['body']['liked']);
});

// Test 5: Admin Like Blocked
test("Admin BLOCKED from liking", function () use ($adminToken, $eventId, $userToken) {
    $comments = req('GET', "events/$eventId/comments", $userToken);
    $commentId = $comments['body']['data'][0]['id'] ?? null;

    $res = req('POST', "comments/$commentId/like", $adminToken);
    return $res['code'] === 403;
});

