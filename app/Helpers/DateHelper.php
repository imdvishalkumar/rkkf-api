<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format date for display
     */
    public static function formatDate($date, string $format = 'Y-m-d'): ?string
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->format($format);
        }

        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get current year
     */
    public static function getCurrentYear(): int
    {
        return (int) date('Y');
    }

    /**
     * Get current month
     */
    public static function getCurrentMonth(): int
    {
        return (int) date('m');
    }

    /**
     * Get month name
     */
    public static function getMonthName(int $month): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return $months[$month] ?? 'Unknown';
    }

    /**
     * Parse date string to Carbon instance
     */
    public static function parseDate($date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if date is in range
     */
    public static function isDateInRange($date, $startDate, $endDate): bool
    {
        $date = self::parseDate($date);
        $start = self::parseDate($startDate);
        $end = self::parseDate($endDate);

        if (!$date || !$start || !$end) {
            return false;
        }

        return $date->between($start, $end);
    }
}



