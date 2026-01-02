<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon', function (Blueprint $table) {
            $table->integer('coupon_id', true);
            $table->string('coupon_txt', 32);
            $table->integer('amount');
            $table->integer('used');
            
            $table->primary('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon');
    }
};

