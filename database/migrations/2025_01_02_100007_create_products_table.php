<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integer('product_id', true);
            $table->string('name', 50);
            $table->string('details', 300);
            $table->string('image1', 500);
            $table->string('image2', 500)->nullable();
            $table->string('image3', 500)->nullable();
            $table->string('belt_ids', 256);
            $table->integer('is_active');
            
            $table->primary('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

