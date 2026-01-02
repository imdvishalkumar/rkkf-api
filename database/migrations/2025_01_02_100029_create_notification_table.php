<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 50);
            $table->string('details', 2560);
            $table->integer('student_id');
            $table->integer('viewed');
            $table->string('type', 50);
            $table->integer('sent');
            $table->timestamp('timestamp')->nullable();
            
            $table->primary('id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};

