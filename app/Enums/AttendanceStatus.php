<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'P';
    case Absent = 'A';
    case Leave = 'L';
    case Fail = 'F';
    case Event = 'E';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Leave => 'On Leave',
            self::Fail => 'Failed',
            self::Event => 'Event',
        };
    }
}
