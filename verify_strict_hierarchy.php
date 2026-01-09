<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Verifying Strict 2-Level Hierarchy...\n";

    $service = app(App\Services\EventCommentService::class);
    $user = App\Models\User::where('role', 'user')->first();
    $event = App\Models\Event::first();

    if (!$user || !$event)
        die("Missing data\n");

    // 1. Create Root Comment (A)
    $root = $service->addComment($event->event_id, $user->user_id, "Root A " . time());
    echo "Root A ID: " . $root->id . "\n";

    // 2. Create Reply to A (B)
    $replyB = $service->addComment($event->event_id, $user->user_id, "Reply B", $root->id);
    echo "Reply B ID: " . $replyB->id . " | Parent: " . $replyB->parent_id . "\n";

    if ($replyB->parent_id != $root->id)
        die("FAIL: B parent != A\n");

    // 3. Create Reply to B (C) - Should attach to A
    $replyC = $service->addComment($event->event_id, $user->user_id, "Reply C", $replyB->id);
    echo "Reply C ID: " . $replyC->id . " | Parent: " . $replyC->parent_id . "\n";

    if ($replyC->parent_id != $root->id)
        die("FAIL: C parent != A (Should be " . $root->id . ")\n");

    // 4. Create Reply to C (D) - Should ALSO attach to A
    $replyD = $service->addComment($event->event_id, $user->user_id, "Reply D", $replyC->id);
    echo "Reply D ID: " . $replyD->id . " | Parent: " . $replyD->parent_id . "\n";

    if ($replyD->parent_id != $root->id)
        die("FAIL: D parent != A (Should be " . $root->id . ")\n");

    echo "SUCCESS: All replies attached to Root A.\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
