<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_attendance', function (Blueprint $table) {
            $table->integer('event_attendance_id', true);
            $table->integer('event_id');
            $table->integer('student_id');
            $table->string('attend', 50);
            $table->integer('user_id');
            
            $table->primary('event_attendance_id');
            $table->index(['event_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendance');
    }
};




