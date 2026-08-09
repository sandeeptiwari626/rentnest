<?php

namespace App\Enums;

enum MaintenanceCategory: string
{
    case Plumbing = 'plumbing';
    case Electrical = 'electrical';
    case Appliance = 'appliance';
    case Internet = 'internet';
    case Cleaning = 'cleaning';
    case Structural = 'structural';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Plumbing => 'Plumbing',
            self::Electrical => 'Electrical',
            self::Appliance => 'Appliance',
            self::Internet => 'Internet',
            self::Cleaning => 'Cleaning',
            self::Structural => 'Structural',
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
