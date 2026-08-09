<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isLandlord() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Select at least one payment to delete.',
            'ids.min' => 'Select at least one payment to delete.',
        ];
    }
}
