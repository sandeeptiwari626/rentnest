<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Vacant = 'vacant';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Vacant => 'Vacant',
            self::Occupied => 'Occupied',
            self::Maintenance => 'Maintenance',
            self::Inactive => 'Inactive',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Vacant => 'neutral',
            self::Occupied => 'success',
            self::Maintenance => 'warning',
            self::Inactive => 'danger',
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
