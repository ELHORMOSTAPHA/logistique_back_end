<?php

namespace App\Http\Requests\Stock;

use App\Models\Depot;
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
            'commentaire' => ['sometimes', 'nullable', 'string', 'max:255'],
            'select_all' => ['sometimes', 'boolean'],
            'excluded_ids' => ['nullable', 'array'],
            'excluded_ids.*' => ['integer'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:stocks,id'],
            'filters' => ['nullable', 'array'],
            'filters.name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.modele' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.marque' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.marque_ids' => ['sometimes', 'nullable', 'array'],
            'filters.marque_ids.*' => ['integer', 'exists:marques,id'],
            'filters.vin' => ['sometimes', 'nullable', 'string', 'max:45'],
            'filters.stock_status_id' => ['sometimes', 'nullable', 'integer', 'exists:stock_statuts,id'],
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

        $validator->after(function (Validator $v): void {
            $depotId = (int) ($this->input('depot_id') ?? 0);
            if ($depotId < 1) {
                return;
            }
            $typeId = Depot::query()->whereKey($depotId)->value('type_depot_id');
            if ((int) $typeId !== 3) {
                return;
            }
            $commentaire = $this->input('commentaire');
            $trimmed = is_string($commentaire) ? trim($commentaire) : '';
            if ($trimmed === '') {
                $v->errors()->add('commentaire', 'Le commentaire est obligatoire pour ce type de dépôt.');
            }
        });
    }
}
