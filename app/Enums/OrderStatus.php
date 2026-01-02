<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 0;
    case SUCCESS = 1;
    case FAILED = 2;
    case CANCELLED = 3;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
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



