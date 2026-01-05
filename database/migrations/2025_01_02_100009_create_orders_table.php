<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->integer('order_id', true);
            $table->integer('counter');
            $table->integer('student_id');
            $table->integer('product_id');
            $table->integer('qty');
            $table->float('p_price');
            $table->string('rp_order_id', 256);
            $table->integer('status');
            $table->date('date');
            $table->string('name_var', 100);
            $table->integer('variation_id');
            $table->integer('flag')->comment('successApi or webhook');
            $table->tinyInteger('flag_delivered');
            $table->tinyInteger('viewed')->default(0);
            
            $table->primary('order_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};




