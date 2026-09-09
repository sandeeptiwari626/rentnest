<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class AcknowledgeLegalNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenant() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notice_id' => ['required', 'integer'],
            'accepted' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'accepted.accepted' => 'Please confirm that you have read and understood the Tenant Portal Notice before continuing.',
        ];
    }
}
