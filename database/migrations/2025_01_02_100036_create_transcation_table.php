<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transcation', function (Blueprint $table) {
            $table->integer('transcation_id', true);
            $table->integer('student_id');
            $table->string('order_id', 50);
            $table->integer('status');
            $table->string('type', 50);
            $table->integer('ref_id');
            $table->integer('amount');
            $table->date('date');
            $table->string('months', 20);
            $table->string('year', 5);
            $table->integer('coupon_id');
            
            $table->primary('transcation_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transcation');
    }
};

