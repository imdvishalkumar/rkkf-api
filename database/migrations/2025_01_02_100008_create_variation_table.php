<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('product_id');
            $table->string('variation', 50);
            $table->integer('price');
            $table->integer('qty');
            
            $table->primary('id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variation');
    }
};







