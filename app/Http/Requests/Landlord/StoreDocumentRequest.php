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

    protected function prepareForValidation(): void
    {
        if ($this->input('documentable_type') === '') {
            $this->merge(['documentable_type' => null]);
        }
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
            'file' => ['required', 'file', 'max:8192', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,txt'],
            'visible_to_tenant' => ['sometimes', 'boolean'],
            'documentable_type' => ['nullable', 'string', Rule::in(['property', 'tenant', 'lease'])],
            'documentable_id' => ['nullable', 'integer'],
            'property_id' => [
                'nullable',
                'required_if:documentable_type,property',
                Rule::exists('properties', 'id')->where('organization_id', $organizationId),
            ],
            'tenant_id' => [
                'nullable',
                'required_if:documentable_type,tenant',
                Rule::exists('tenants', 'id')->where('organization_id', $organizationId),
            ],
            'lease_id' => [
                'nullable',
                'required_if:documentable_type,lease',
                Rule::exists('leases', 'id')->where('organization_id', $organizationId),
            ],
        ];
    }
}
