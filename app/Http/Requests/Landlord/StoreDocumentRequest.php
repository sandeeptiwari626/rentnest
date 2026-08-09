<?php

namespace App\Http\Requests\Landlord;

use App\Enums\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(DocumentType::class)],
            'file' => ['required', 'file', 'max:15360'],
            'visible_to_tenant' => ['sometimes', 'boolean'],
            'documentable_type' => ['nullable', 'string', Rule::in(['property', 'tenant', 'lease'])],
            'documentable_id' => ['nullable', 'integer', 'required_with:documentable_type'],
            'property_id' => [
                'nullable',
                Rule::exists('properties', 'id')->where('organization_id', $organizationId),
            ],
            'tenant_id' => [
                'nullable',
                Rule::exists('tenants', 'id')->where('organization_id', $organizationId),
            ],
            'lease_id' => [
                'nullable',
                Rule::exists('leases', 'id')->where('organization_id', $organizationId),
            ],
        ];
    }
}
