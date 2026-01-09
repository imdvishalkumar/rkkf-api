<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix incorrect existing records: Flatten deep nested replies to 2nd level
        // UPDATE event_comments
        // SET parent_id = (
        //     SELECT parent_id FROM (
        //         SELECT id, parent_id FROM event_comments
        //     ) t
        //     WHERE t.id = event_comments.parent_id
        // )
        // WHERE parent_id IN (
        //     SELECT id FROM event_comments WHERE parent_id IS NOT NULL
        // );

        // Fix incorrect existing records using Self-Join (MySQL compatible)
        // This moves grandchildren to be children of the root (Grandparent)

        DB::statement("
            UPDATE event_comments AS child
            JOIN event_comments AS parent ON child.parent_id = parent.id
            SET child.parent_id = parent.parent_id
            WHERE parent.parent_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily reverse this data change without a backup
    }
};
