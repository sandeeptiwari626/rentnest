<?php

namespace App\Http\Requests\Tenant;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenant() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', Rule::enum(MaintenanceCategory::class)],
            'priority' => ['required', Rule::enum(MaintenancePriority::class)],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photos.*.image' => 'Each photo must be an image file.',
            'photos.*.max' => 'Each photo may not be larger than 5 MB.',
        ];
    }
}
