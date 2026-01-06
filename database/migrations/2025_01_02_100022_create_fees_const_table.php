<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees_const', function (Blueprint $table) {
            $table->integer('fc_id');
            $table->string('type', 10);
            $table->bigInteger('amount');
            
            $table->primary('fc_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_const');
    }
};







