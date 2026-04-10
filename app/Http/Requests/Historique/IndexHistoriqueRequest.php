<?php

namespace App\Http\Requests\Historique;

use Illuminate\Foundation\Http\FormRequest;

class IndexHistoriqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('paginated')) {
            return;
        }

        $value = $this->input('paginated');
        if (! is_string($value)) {
            return;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($normalized !== null) {
            $this->merge(['paginated' => $normalized]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'action' => ['sometimes', 'nullable', 'string', 'max:255'],
            'table_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'record_id' => ['sometimes', 'nullable', 'integer'],
            'keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
            'paginated' => ['sometimes', 'nullable', 'boolean'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sort_by' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
