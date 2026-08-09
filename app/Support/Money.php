<?php

namespace App\Support;

use NumberFormatter;

class Money
{
    public static function format(float|int|string|null $amount, string $currency = 'INR'): string
    {
        $value = (float) ($amount ?? 0);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);

            return $formatter->formatCurrency($value, $currency) ?: self::fallback($value);
        }

        return self::fallback($value);
    }

    protected static function fallback(float $value): string
    {
        return '₹'.number_format($value, 2, '.', ',');
    }
}
