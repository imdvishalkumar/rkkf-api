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
        Schema::create('event_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_comment_id');
            $table->integer('user_id');
            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('event_comment_id')->references('id')->on('event_comments')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            // Unique constraint to prevent duplicate likes
            $table->unique(['event_comment_id', 'user_id'], 'unique_comment_user_like');

            // Indexes
            $table->index('event_comment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_comment_likes');
    }
};
