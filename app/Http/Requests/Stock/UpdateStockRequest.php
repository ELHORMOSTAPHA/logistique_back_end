<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
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
        $id = $this->route('stock')?->id ?? $this->route('stock');

        return [
            'marque' => ['sometimes', 'nullable', 'string', 'max:45'],
            'numero_commande' => ['sometimes', 'nullable', 'string', 'max:45'],
            'modele' => ['sometimes', 'nullable', 'string', 'max:45'],
            /** @deprecated Préférer `finition` — alias historique. */
            'version' => ['sometimes', 'nullable', 'string', 'max:45'],
            'finition' => ['sometimes', 'nullable', 'string', 'max:45'],
            'vin' => ['sometimes', 'nullable', 'string', 'max:45', 'unique:stocks,vin,'.$id],
            'color_ex' => ['sometimes', 'nullable', 'string', 'max:45'],
            'color_ex_code' => ['sometimes', 'nullable', 'string', 'max:45'],
            'color_int' => ['sometimes', 'nullable', 'string', 'max:45'],
            'color_int_code' => ['sometimes', 'nullable', 'string', 'max:45'],
            'client' => ['sometimes', 'nullable', 'string', 'max:120'],
            'type_client' => ['sometimes', 'nullable', 'string', 'max:45'],
            'PGEO' => ['sometimes', 'nullable', 'string', 'max:45'],
            'options' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'vendeur' => ['sometimes', 'nullable', 'string', 'max:120'],
            'site_affecte' => ['sometimes', 'nullable', 'string', 'max:120'],
            'date_creation_commande' => ['sometimes', 'nullable', 'date'],
            'date_arrivage_prevu' => ['sometimes', 'nullable', 'date'],
            'date_arrivage_reelle' => ['sometimes', 'nullable', 'date'],
            'date_affectation' => ['sometimes', 'nullable', 'date'],
            'depot_id' => ['sometimes', 'nullable', 'integer', 'exists:depots,id'],
            'stock_status_id' => ['sometimes', 'nullable', 'integer', 'exists:stock_statuses,id'],
            'statut' => ['sometimes', 'nullable', 'string', 'max:45'],
            'numero_lot' => ['sometimes', 'nullable', 'string', 'max:45'],
            'numero_arrivage' => ['sometimes', 'nullable', 'string', 'max:45'],
            'lot_id' => ['sometimes', 'nullable', 'integer', 'exists:lots,id'],
            'combinaison_rare' => ['sometimes', 'boolean'],
        ];
    }
}
