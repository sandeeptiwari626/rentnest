<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Acknowledged => 'Acknowledged',
            self::InProgress => 'In Progress',
            self::Waiting => 'Waiting',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::Acknowledged => 'warning',
            self::InProgress => 'warning',
            self::Waiting => 'neutral',
            self::Resolved => 'success',
            self::Closed => 'neutral',
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
