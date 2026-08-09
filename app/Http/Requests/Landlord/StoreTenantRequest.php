<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTenantRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->where(
                    fn ($query) => $query->where('organization_id', $organizationId)
                ),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'create_account' => ['sometimes', 'boolean'],
            'password' => [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }
}
