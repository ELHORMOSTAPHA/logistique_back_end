<?php

namespace App\Http\Requests\Depot;

use App\DTOs\Depot\ListDepotDto;
use Illuminate\Foundation\Http\FormRequest;

class IndexDepotRequest extends FormRequest
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
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'created_at' => ['sometimes', 'nullable', 'date'],
            'updated_at' => ['sometimes', 'nullable', 'date'],
            'deleted_at' => ['sometimes', 'nullable', 'date'],
            'created_by' => ['sometimes', 'nullable', 'integer'],
            'deleted_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
            'paginated' => ['sometimes', 'nullable', 'boolean'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sort_by' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function toFilterDto(): ListDepotDto
    {
        return ListDepotDto::fromArray($this->validated());
    }
}
