<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gmail_acc', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('email', 50);
            $table->string('password', 50);
            
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gmail_acc');
    }
};








