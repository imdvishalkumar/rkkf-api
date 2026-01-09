<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Verifying Comment Count...\n";

    // 1. Get Event and User
    $event = App\Models\Event::first();
    $user = App\Models\User::where('role', 'user')->first();

    if (!$event || !$user)
        die("Missing event or user\n");

    // 2. Clear existing comments for this event (optional, or just count them)
    // Actually, let's just count current via DB
    $dbCount = App\Models\EventComment::where('event_id', $event->event_id)->where('is_active', true)->count();
    echo "Current DB Count: $dbCount\n";

    // 3. Add a new comment via Service (simulating API)
    $service = app(App\Services\EventCommentService::class);
    $service->addComment($event->event_id, $user->user_id, "Count Test " . time());

    $newDbCount = App\Models\EventComment::where('event_id', $event->event_id)->where('is_active', true)->count();
    echo "New DB Count: $newDbCount\n";

    // 4. Hit API
    $token = $user->createToken('count-test')->plainTextToken;
    $url = "http://192.168.1.6:8080/api/events/" . $event->event_id;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);

    $res = curl_exec($ch);
    $data = json_decode($res, true);
    curl_close($ch);

    if (isset($data['data']['comments'])) {
        $apiCount = $data['data']['comments'];
        echo "API Count: $apiCount\n";

        if ($apiCount == $newDbCount) {
            echo "SUCCESS: API count matches DB count.\n";
        } else {
            echo "FAIL: API count ($apiCount) != DB count ($newDbCount).\n";
            // Check if legacy column is interfering?
            // echo "Raw Response: " . substr($res, 0, 500) . "\n";
        }
    } else {
        echo "FAIL: 'comments' field missing in response.\n";
        print_r($data);
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
