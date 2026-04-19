<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkChangeDepotStockRequest extends FormRequest
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
            'depot_id' => ['required', 'integer', 'exists:depots,id'],
            'select_all' => ['sometimes', 'boolean'],
            'excluded_ids' => ['nullable', 'array'],
            'excluded_ids.*' => ['integer'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:stocks,id'],
            'filters' => ['nullable', 'array'],
            'filters.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.modele' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.vin' => ['sometimes', 'nullable', 'string', 'max:45'],
            'filters.reserved' => ['sometimes', 'nullable'],
            'filters.depot_id' => ['sometimes', 'nullable', 'integer', 'exists:depots,id'],
            'filters.lot_id' => ['sometimes', 'nullable', 'integer'],
            'filters.from' => ['sometimes', 'nullable', 'date'],
            'filters.to' => ['sometimes', 'nullable', 'date'],
            'filters.sort_by' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.sort_order' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($this->boolean('select_all')) {
                return;
            }
            $ids = $this->input('ids', []);
            if (! is_array($ids) || $ids === []) {
                $v->errors()->add('ids', 'Sélectionnez au moins un véhicule ou utilisez la sélection globale.');
            }
        });
    }
}
