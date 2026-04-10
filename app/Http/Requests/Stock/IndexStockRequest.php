<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class IndexStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Axios (and most clients) send booleans in query strings as "true" / "false", which Laravel's
     * `boolean` rule rejects (it only accepts true, false, 1, 0, "1", "0").
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
            'modele' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vin' => ['sometimes', 'nullable', 'string', 'max:45'],
            'reserved' => ['sometimes', 'nullable'],
            'depot_id' => ['sometimes', 'nullable', 'integer', 'exists:depots,id'],
            'lot_id' => ['sometimes', 'nullable', 'integer', 'exists:lots,id'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
            'sort_by' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'nullable', 'string', 'max:255'],
            'paginated' => ['sometimes', 'nullable', 'boolean'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
