<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch', function (Blueprint $table) {
            $table->integer('branch_id', true);
            $table->string('name', 50);
            $table->string('days', 100);
            $table->integer('fees');
            $table->integer('late');
            $table->integer('discount');
            
            $table->primary('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};








