<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('belt', function (Blueprint $table) {
            $table->integer('belt_id', true);
            $table->string('name', 20);
            $table->integer('code');
            $table->integer('priority');
            $table->integer('exam_fees');
            
            $table->primary('belt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('belt');
    }
};

