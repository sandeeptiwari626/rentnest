<?php

namespace App\Enums;

enum PropertyType: string
{
    case Apartment = 'apartment';
    case House = 'house';
    case Villa = 'villa';
    case Shop = 'shop';
    case Office = 'office';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Apartment => 'Apartment',
            self::House => 'House',
            self::Villa => 'Villa',
            self::Shop => 'Shop',
            self::Office => 'Office',
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
