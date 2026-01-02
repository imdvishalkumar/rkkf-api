<?php

namespace App\Enums;

enum PaymentMode: string
{
    case CASH = 'cash';
    case ONLINE = 'online';
    case APP = 'app';
    case RAZORPAY = 'razorpay';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::ONLINE => 'Online',
            self::APP => 'App',
            self::RAZORPAY => 'Razorpay',
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }

    public function isOnline(): bool
    {
        return $this === self::ONLINE || $this === self::APP || $this === self::RAZORPAY;
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



