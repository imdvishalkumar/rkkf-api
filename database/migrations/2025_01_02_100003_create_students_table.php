<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->integer('student_id', true);
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->integer('gender');
            $table->string('email', 320);
            $table->string('password', 100);
            $table->integer('belt_id');
            $table->string('dadno', 10)->nullable();
            $table->string('dadwp', 10)->nullable();
            $table->string('momno', 10)->nullable();
            $table->string('momwp', 10)->nullable();
            $table->string('selfno', 10)->nullable();
            $table->string('selfwp', 10)->nullable();
            $table->date('dob');
            $table->date('doj');
            $table->string('address', 500);
            $table->integer('branch_id');
            $table->string('pincode', 6);
            $table->string('std', 50)->default('N/A');
            $table->integer('active');
            $table->string('reset_link_token', 40)->nullable();
            $table->dateTime('exp_date')->nullable();
            $table->string('profile_img', 500);
            $table->integer('call_flag')->default(0)->comment('0 = Not Call, 1 = Called');
            
            $table->primary('student_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

