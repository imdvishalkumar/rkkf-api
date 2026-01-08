<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_fees', function (Blueprint $table) {
            $table->integer('exam_fees_id', true);
            $table->integer('exam_id');
            $table->integer('student_id');
            $table->date('date');
            $table->string('mode', 11);
            $table->string('rp_order_id', 256);
            $table->integer('status');
            $table->integer('amount');
            $table->integer('exam_belt_id');
            $table->integer('up');
            $table->integer('dump');
            
            $table->primary('exam_fees_id');
            $table->index(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_fees');
    }
};









