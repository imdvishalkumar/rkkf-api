<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fastrack', function (Blueprint $table) {
            $table->integer('fastrack_id', true);
            $table->integer('student_id');
            $table->integer('from_belt_id');
            $table->integer('to_belt_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('months_count');
            $table->integer('total_fees');
            $table->integer('total_hours');
            
            $table->primary('fastrack_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fastrack');
    }
};








