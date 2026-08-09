<?php

namespace App\Http\Requests\Landlord;

use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaintenanceRequest extends FormRequest
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
        return [
            'status' => ['required', Rule::enum(MaintenanceStatus::class)],
            'priority' => ['nullable', Rule::enum(MaintenancePriority::class)],
            'scheduled_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
