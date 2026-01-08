<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temp_exam', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id');
            $table->integer('belt_id');
            $table->date('date');
            $table->string('certificate_no', 256);
            $table->timestamp('inserted_at')->useCurrent();
            
            $table->primary('id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temp_exam');
    }
};








