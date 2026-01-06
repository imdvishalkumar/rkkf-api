<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fastrack_attendance', function (Blueprint $table) {
            $table->integer('fast_atten_id', true);
            $table->integer('student_id');
            $table->integer('hours');
            $table->date('date');
            $table->integer('branch_id');
            $table->integer('user_id');
            
            $table->primary('fast_atten_id');
            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fastrack_attendance');
    }
};







