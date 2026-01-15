<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branch', function (Blueprint $table) {
            $table->text('address')->nullable()->after('discount');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('zip_code', 20)->nullable()->after('state');
            $table->string('phone', 20)->nullable()->after('zip_code');
            $table->string('email', 100)->nullable()->after('phone');
            $table->decimal('latitude', 10, 8)->nullable()->after('email');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->text('map_link')->nullable()->after('longitude');
            $table->boolean('is_active')->default(true)->after('map_link');
            // Adding timestamps if they don't exist, though the original creating didn't have them. 
            // Eloquent expects them by default unless $timestamps = false;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'city',
                'state',
                'zip_code',
                'phone',
                'email',
                'latitude',
                'longitude',
                'map_link',
                'is_active',
                'created_at',
                'updated_at'
            ]);
        });
    }
};
