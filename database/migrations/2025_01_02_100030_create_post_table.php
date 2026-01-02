<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 100);
            $table->string('description', 12288);
            $table->timestamp('created')->useCurrent();
            $table->integer('is_deleted')->default(0);
            
            $table->primary('id');
            $table->index('is_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post');
    }
};

