<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::where('role', 'user')->first();
    if (!$user)
        die("User not found\n");

    $token = $user->createToken('debug')->plainTextToken;
    $event = App\Models\Event::first();
    $eventId = $event->event_id;

    $url = "http://192.168.1.6:8080/api/events/$eventId/comments";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['comment' => 'Debug']));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    echo "Code: " . $info['http_code'] . "\n";
    echo "Body: " . substr($res, 0, 500) . "\n"; // First 500 chars

} catch (Throwable $e) {
    echo "Script Error: " . $e->getMessage() . "\n";
}
