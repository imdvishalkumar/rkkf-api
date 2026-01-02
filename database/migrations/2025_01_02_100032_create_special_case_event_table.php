<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_case_event', function (Blueprint $table) {
            $table->integer('special_id', true);
            $table->integer('student_id');
            $table->integer('event_id');
            $table->integer('eligible');
            
            $table->primary('special_id');
            $table->index(['student_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_case_event');
    }
};

