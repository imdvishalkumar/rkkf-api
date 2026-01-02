<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam', function (Blueprint $table) {
            $table->integer('exam_id', true);
            $table->string('name', 20);
            $table->date('date');
            $table->integer('sessions_count');
            $table->integer('fees');
            $table->date('fess_due_date');
            $table->string('location', 100);
            $table->date('to_criteria')->nullable();
            $table->date('from_criteria')->nullable();
            $table->tinyInteger('isPublished');
            
            $table->primary('exam_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam');
    }
};

