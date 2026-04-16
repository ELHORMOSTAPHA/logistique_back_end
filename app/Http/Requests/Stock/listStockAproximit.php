<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class listStockAproximit extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize query-string booleans like "true"/"false" for `paginated`.
     */
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
            'modele' => ['required', 'string', 'max:45'],
            'version' => ['required', 'string', 'max:45'],
            'finition' => ['required', 'string', 'max:45'],
            'color_ex' => ['required', 'string', 'max:45'],
            'color_int' => ['required', 'string', 'max:45'],
            'paginated' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
