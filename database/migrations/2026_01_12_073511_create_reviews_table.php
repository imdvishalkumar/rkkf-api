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
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id');
                $table->integer('rating')->default(5);
                $table->text('review')->nullable();
                $table->string('image')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();

                $table->index('student_id');
                $table->index('order_id');
                $table->index('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
