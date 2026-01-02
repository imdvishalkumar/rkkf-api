<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'P';
    case ABSENT = 'A';
    case LEAVE = 'L';

    public function label(): string
    {
        return match($this) {
            self::PRESENT => 'Present',
            self::ABSENT => 'Absent',
            self::LEAVE => 'Leave',
        };
    }

    public function isPresent(): bool
    {
        return $this === self::PRESENT;
    }

    public function isAbsent(): bool
    {
        return $this === self::ABSENT;
    }

    public function isLeave(): bool
    {
        return $this === self::LEAVE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}



