<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('post_id');
            $table->string('path', 256);
            $table->string('type', 10);
            
            $table->primary('id');
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};









