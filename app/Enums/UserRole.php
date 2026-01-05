<?php

namespace App\Enums;

enum UserRole: int
{
    case USER = 0;
    case ADMIN = 1;
    case INSTRUCTOR = 2;

    public function label(): string
    {
        return match($this) {
            self::USER => 'User',
            self::ADMIN => 'Admin',
            self::INSTRUCTOR => 'Instructor',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isInstructor(): bool
    {
        return $this === self::INSTRUCTOR;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }
}



