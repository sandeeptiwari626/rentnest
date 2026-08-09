<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Maintenance = 'maintenance';
    case Utilities = 'utilities';
    case Repairs = 'repairs';
    case Taxes = 'taxes';
    case Insurance = 'insurance';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Maintenance => 'Maintenance',
            self::Utilities => 'Utilities',
            self::Repairs => 'Repairs',
            self::Taxes => 'Taxes',
            self::Insurance => 'Insurance',
            self::Other => 'Other',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $case) => [$case->value => $case->label()]
        )->all();
    }
}
