<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Landlord = 'landlord';
    case Tenant = 'tenant';

    public function label(): string
    {
        return match ($this) {
            self::Landlord => 'Landlord',
            self::Tenant => 'Tenant',
        };
    }
}
