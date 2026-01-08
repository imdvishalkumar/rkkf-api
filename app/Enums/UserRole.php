<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case INSTRUCTOR = 'instructor';

    /**
     * Get human-readable label for the role
     */
    public function label(): string
    {
        return match ($this) {
            self::USER => 'User',
            self::ADMIN => 'Admin',
            self::INSTRUCTOR => 'Instructor',
        };
    }

    /**
     * Get Spatie role name for the internal role
     */
    public function spatieRole(): string
    {
        return match ($this) {
            self::USER => 'student',
            self::INSTRUCTOR => 'instructor',
            self::ADMIN => 'admin',
        };
    }

    /**
     * Check if role is admin
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role is instructor
     */
    public function isInstructor(): bool
    {
        return $this === self::INSTRUCTOR;
    }

    /**
     * Get all role values as strings
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Convert string value to enum (tryFrom returns null if not found)
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}



