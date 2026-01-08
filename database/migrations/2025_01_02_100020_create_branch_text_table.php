<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_text', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('value');
            
            $table->primary('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_text');
    }
};









