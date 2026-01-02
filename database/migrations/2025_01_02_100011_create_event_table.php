<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event', function (Blueprint $table) {
            $table->integer('event_id', true);
            $table->string('name', 50);
            $table->date('from_date');
            $table->date('to_date');
            $table->string('venue', 50);
            $table->string('type', 50);
            $table->string('description', 250);
            $table->integer('fees');
            $table->date('fees_due_date');
            $table->integer('penalty');
            $table->date('penalty_due_date');
            $table->tinyInteger('isPublished')->nullable();
            
            $table->primary('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event');
    }
};

