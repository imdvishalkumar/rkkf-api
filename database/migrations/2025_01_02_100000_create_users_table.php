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
            $table->enum('role', ['user', 'admin', 'instructor'])->default('user');
            
            $table->primary('user_id');
            $table->index('email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};







