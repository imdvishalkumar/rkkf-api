<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquire', function (Blueprint $table) {
            $table->integer('enquire_id', true);
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->integer('gender');
            $table->string('email', 320);
            $table->date('dob');
            $table->date('doj');
            $table->string('dadno', 10);
            $table->string('dadwp', 10);
            $table->string('momno', 10);
            $table->string('momwp', 10);
            $table->string('selfno', 10);
            $table->string('selfwp', 10);
            $table->string('address', 500);
            $table->integer('branch_id');
            $table->string('pincode', 6);
            $table->string('order_id', 50);
            $table->integer('amount');
            $table->string('payment_id', 100);
            $table->integer('payment_status');
            $table->integer('inserted_status');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->useCurrent();
            $table->integer('direct_entry')->default(0);
            
            $table->primary('enquire_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquire');
    }
};







