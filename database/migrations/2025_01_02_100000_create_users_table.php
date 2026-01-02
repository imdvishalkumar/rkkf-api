<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('user_id', true);
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('mobile', 15);
            $table->string('email', 100);
            $table->string('password', 16);
            $table->integer('role');
            
            $table->primary('user_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

