<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attendance1', function (Blueprint $table) {
            $table->integer('exam_attendance_id', true);
            $table->integer('exam_id');
            $table->integer('student_id');
            $table->string('attend', 5);
            $table->integer('user_id');
            $table->string('certificate_no', 50);
            
            $table->primary('exam_attendance_id');
            $table->index(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attendance1');
    }
};







