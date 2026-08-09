<?php

namespace App\Enums;

enum DocumentType: string
{
    case LeaseAgreement = 'lease_agreement';
    case Identity = 'identity';
    case Property = 'property';
    case RentReceipt = 'rent_receipt';
    case MaintenanceInvoice = 'maintenance_invoice';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LeaseAgreement => 'Lease Agreement',
            self::Identity => 'Identity Document',
            self::Property => 'Property Document',
            self::RentReceipt => 'Rent Receipt',
            self::MaintenanceInvoice => 'Maintenance Invoice',
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
