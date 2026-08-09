<?php

namespace App\Enums;

enum LeaseStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Expiring = 'expiring';
    case Expired = 'expired';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Expiring => 'Expiring',
            self::Expired => 'Expired',
            self::Terminated => 'Terminated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Active => 'success',
            self::Expiring => 'warning',
            self::Expired => 'danger',
            self::Terminated => 'neutral',
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
