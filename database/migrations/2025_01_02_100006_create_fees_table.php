<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->integer('fee_id', true);
            $table->integer('student_id');
            $table->integer('months');
            $table->integer('year');
            $table->date('date');
            $table->integer('amount');
            $table->integer('coupon_id');
            $table->integer('additional');
            $table->integer('disabled');
            $table->string('mode', 50);
            $table->string('remarks', 512);
            $table->integer('up');
            $table->integer('dump');
            $table->string('new_remarks', 512);
            $table->integer('call_flag')->default(0)->comment('0 = Not Call, 1 = Called');
            
            $table->primary('fee_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};









