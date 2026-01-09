<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Verifying Response Format...\n";

    $user = App\Models\User::where('role', 'user')->first();
    $event = App\Models\Event::first();

    if (!$user || !$event)
        die("Missing user/event\n");

    $token = $user->createToken('fmt-test')->plainTextToken;
    $url = "http://192.168.1.6:8080/api/events/" . $event->event_id . "/comments";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);

    $res = curl_exec($ch);
    $data = json_decode($res, true);
    curl_close($ch);

    $keys = ['status', 'message', 'data', 'errors', 'meta'];
    $missing = [];
    foreach ($keys as $key) {
        if (!array_key_exists($key, $data)) {
            $missing[] = $key;
        }
    }

    if (empty($missing)) {
        echo "SUCCESS: Response contains all required keys.\n";
        echo "Status: " . ($data['status'] ? 'true' : 'false') . "\n";
        echo "Message: " . $data['message'] . "\n";
    } else {
        echo "FAIL: Missing keys: " . implode(', ', $missing) . "\n";
        echo "Raw: " . substr($res, 0, 500) . "\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
