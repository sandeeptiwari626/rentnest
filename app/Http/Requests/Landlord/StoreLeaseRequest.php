<?php

namespace App\Http\Requests\Landlord;

use App\Enums\LeaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isLandlord() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $organizationId = $this->user()?->current_organization_id;

        return [
            'property_id' => [
                'required',
                Rule::exists('properties', 'id')->where('organization_id', $organizationId),
            ],
            'unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('organization_id', $organizationId),
            ],
            'tenant_id' => [
                'required',
                Rule::exists('tenants', 'id')->where('organization_id', $organizationId),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'rent_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'notice_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'status' => ['nullable', Rule::enum(LeaseStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'lease_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
