<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 50);
            $table->string('post', 50);
            $table->string('image', 256);
            
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team');
    }
};








