<?php

namespace App\Models;

use App\Enums\LeaseStatus;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'organization_id',
    'property_id',
    'unit_id',
    'tenant_id',
    'start_date',
    'end_date',
    'monthly_rent',
    'security_deposit',
    'rent_due_day',
    'notice_period_days',
    'status',
    'lease_document_path',
    'notes',
])]
class Lease extends Model
{
    use BelongsToOrganization;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LeaseStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_rent' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'rent_due_day' => 'integer',
            'notice_period_days' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return HasMany<RentPayment, $this>
     */
    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }
}
