<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_table', function (Blueprint $table) {
            $table->integer('leave_id', true);
            $table->integer('student_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->string('reason', 500);
            
            $table->primary('leave_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_table');
    }
};








