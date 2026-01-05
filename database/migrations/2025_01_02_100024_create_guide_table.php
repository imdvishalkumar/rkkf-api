<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 100);
            $table->string('link', 512);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('is_deleted')->default(0);
            $table->integer('created_by');
            
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide');
    }
};




