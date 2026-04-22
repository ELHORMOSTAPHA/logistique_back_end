<?php

namespace App\Http\Requests\Historique;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHistoriqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'string', 'max:45'],
            'action' => ['sometimes', 'nullable', 'string', 'max:45'],
            'table_name' => ['sometimes', 'nullable', 'string', 'max:45'],
            'record_id' => ['sometimes', 'nullable', 'integer'],
            'old_value' => ['sometimes', 'nullable', 'string'],
            'new_value' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'ip_address' => ['sometimes', 'nullable', 'string', 'max:64'],
            'http_method' => ['sometimes', 'nullable', 'string', 'max:12'],
            'request_path' => ['sometimes', 'nullable', 'string', 'max:512'],
            'user_agent' => ['sometimes', 'nullable', 'string', 'max:65535'],
        ];
    }
}
