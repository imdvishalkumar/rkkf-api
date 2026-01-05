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
        Schema::table('event', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type'); // Separate from existing type
            $table->text('image')->nullable()->after('name');
            $table->string('subtitle')->nullable()->after('name');
            $table->integer('likes')->default(0)->after('description');
            $table->integer('comments')->default(0)->after('likes');
            $table->integer('shares')->default(0)->after('comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropColumn(['category', 'image', 'subtitle', 'likes', 'comments', 'shares']);
        });
    }
};
