<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->integer('device_id', true);
            $table->integer('student_id');
            $table->string('player_id', 256);
            $table->string('device_type', 10);
            $table->integer('is_active');
            
            $table->primary('device_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};




