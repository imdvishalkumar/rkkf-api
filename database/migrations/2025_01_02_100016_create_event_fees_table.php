<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_fees', function (Blueprint $table) {
            $table->integer('event_fees_id', true);
            $table->integer('event_id');
            $table->integer('student_id');
            $table->date('date');
            $table->string('mode', 20);
            $table->string('rp_order_id', 256);
            $table->integer('status');
            $table->integer('amount');
            
            $table->primary('event_fees_id');
            $table->index(['event_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_fees');
    }
};







