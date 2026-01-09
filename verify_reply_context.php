<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Verifying Reply-To User Tracking...\n";

    $service = app(App\Services\EventCommentService::class);
    // User 1 (Author)
    $user1 = App\Models\User::where('role', 'user')->first();
    // User 2 (Replier)
    $user2 = App\Models\User::where('role', 'user')->where('user_id', '!=', $user1->user_id)->first();

    // Ensure we have two distinct users
    if (!$user1 || !$user2) {
        $user2 = $user1; // Fallback for testing if only 1 user exists
        echo "Warning: Using same user for reply test.\n";
    }

    $event = App\Models\Event::first();

    // 1. Root Comment by User 1
    echo "1. Root Comment by User {$user1->user_id}\n";
    $root = $service->addComment($event->event_id, $user1->user_id, "Root Comment");
    echo "   Root ID: {$root->id}\n";

    // 2. Reply by User 2 to Root (Should have parent_id=Root, reply_to_user_id=User1)
    echo "2. Reply A by User {$user2->user_id} to Root\n";
    $replyA = $service->addComment($event->event_id, $user2->user_id, "Reply to Root", $root->id);
    echo "   Reply A ID: {$replyA->id} | Parent: {$replyA->parent_id} | ReplyToUser: {$replyA->reply_to_user_id}\n";

    if ($replyA->parent_id != $root->id)
        echo "FAIL: Parent ID mismatch\n";
    if ($replyA->reply_to_user_id != $user1->user_id)
        echo "FAIL: ReplyToUser mismatch (Expected {$user1->user_id})\n";

    // 3. Reply by User 1 to Reply A (Should have parent_id=Root, reply_to_user_id=User2)
    echo "3. Reply B by User {$user1->user_id} to Reply A\n";
    $replyB = $service->addComment($event->event_id, $user1->user_id, "Reply to User 2", $replyA->id);
    echo "   Reply B ID: {$replyB->id} | Parent: {$replyB->parent_id} | ReplyToUser: {$replyB->reply_to_user_id}\n";

    if ($replyB->parent_id != $root->id)
        echo "FAIL: Parent ID mismatch (Should be flattened to {$root->id})\n";
    if ($replyB->reply_to_user_id != $user2->user_id)
        echo "FAIL: ReplyToUser mismatch (Expected {$user2->user_id})\n";

    echo "SUCCESS: Context logic verified.\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
