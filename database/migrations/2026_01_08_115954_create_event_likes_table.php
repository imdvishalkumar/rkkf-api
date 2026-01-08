<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_likes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('event_id');
            $table->boolean('is_liked')->default(true);
            $table->timestamps();

            // Unique constraint: one like record per user per event
            $table->unique(['user_id', 'event_id']);

            // Add indexes for better performance
            $table->index('user_id');
            $table->index('event_id');
            $table->index('is_liked');
        });

        // Add foreign keys separately (if tables exist)
        try {
            Schema::table('event_likes', function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might fail if table doesn't exist, continue
        }

        try {
            Schema::table('event_likes', function (Blueprint $table) {
                $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might fail if table doesn't exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_likes');
    }
};
