<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('event_comments', function (Blueprint $table) {
            $table->integer('reply_to_user_id')->nullable()->after('parent_id');
            // Assuming users table uses 'user_id' as primary key based on previous context
            // checking User.php... yes, primaryKey = 'user_id'
            $table->foreign('reply_to_user_id')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_comments', function (Blueprint $table) {
            $table->dropForeign(['reply_to_user_id']);
            $table->dropColumn('reply_to_user_id');
        });
    }
};
