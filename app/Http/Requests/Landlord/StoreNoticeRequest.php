<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isLandlord() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'property_id' => $this->input('property_id') ?: null,
            'tenant_id' => $this->input('tenant_id') ?: null,
            'expiry_date' => $this->input('expiry_date') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $organizationId = $this->user()?->current_organization_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'property_id' => [
                'nullable',
                Rule::exists('properties', 'id')->where('organization_id', $organizationId),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('tenants', 'id')->where('organization_id', $organizationId),
            ],
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
        ];
    }
}
