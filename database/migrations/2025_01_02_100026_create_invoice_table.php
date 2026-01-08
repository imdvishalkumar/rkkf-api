<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->integer('invoice_id', true);
            $table->integer('ref_id');
            $table->string('type', 50);
            
            $table->primary('invoice_id');
            $table->index(['ref_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};









