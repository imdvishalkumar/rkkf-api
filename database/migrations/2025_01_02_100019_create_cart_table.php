<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->integer('cart_id', true);
            $table->integer('student_id');
            $table->integer('product_id');
            $table->integer('qty');
            $table->integer('variation_id');

            $table->primary('cart_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};








