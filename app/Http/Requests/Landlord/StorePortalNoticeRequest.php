<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortalNoticeRequest extends FormRequest
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
            'version' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d+(\.\d+)*$/',
                Rule::unique('tenant_portal_notices', 'version')->where(
                    fn ($query) => $query->where('organization_id', $organizationId)
                ),
            ],
            'activate' => ['sometimes', 'boolean'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.heading' => ['required', 'string', 'max:255'],
            'sections.*.body' => ['required', 'string', 'max:10000'],
        ];
    }
}
