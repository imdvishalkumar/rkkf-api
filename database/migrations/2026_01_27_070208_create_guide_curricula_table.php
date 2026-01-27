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
        Schema::create('guide_curricula', function (Blueprint $table) {
            $table->id('id');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('language')->default('English'); // Grouping by language
            $table->string('icon')->nullable(); // For the red icon
            $table->string('file_url')->nullable(); // PDF or content link
            $table->string('type')->default('faq'); // faq, curriculum, etc.
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_curricula');
    }
};
