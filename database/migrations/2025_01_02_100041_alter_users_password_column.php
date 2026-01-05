<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Alter the password column to support bcrypt hashed passwords (60 characters)
     * while maintaining backward compatibility with existing plain text passwords
     */
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal package
        // Change password column from VARCHAR(16) to VARCHAR(255)
        // This supports:
        // - Plain text passwords (legacy, up to 255 chars)
        // - Bcrypt hashed passwords (60 chars)
        // - Future password hashing algorithms
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original VARCHAR(16)
        // WARNING: This will truncate any hashed passwords!
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(16) NOT NULL');
    }
};

