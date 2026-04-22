<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class ImportStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $date = ['nullable', 'date'];

        return [
            'import_mode' => ['nullable', 'string', 'in:stock_feed,vin_update'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*' => ['required', 'array'],
            'rows.*.vin' => ['nullable','unique:stocks,vin', 'string', 'max:45'],
            'rows.*.numero_commande' => ['nullable', 'string', 'max:45'],
            'rows.*.statut' => ['nullable', 'string', 'max:45'],
            'rows.*.date_arrivage_prevu' => $date,
            'rows.*.client' => ['nullable', 'string', 'max:120'],
            'rows.*.type_client' => ['nullable', 'string', 'max:45'],
            'rows.*.PGEO' => ['nullable', 'string', 'max:45'],
            'rows.*.numero_lot' => ['nullable', 'string', 'max:45'],
            'rows.*.marque' => ['nullable', 'string', 'max:45'],
            'rows.*.modele' => ['nullable', 'string', 'max:45'],
            'rows.*.finition' => ['nullable', 'string', 'max:45'],
            'rows.*.options' => ['nullable', 'string'],
            'rows.*.color_ex' => ['nullable', 'string', 'max:45'],
            'rows.*.color_int' => ['nullable', 'string', 'max:45'],
            'rows.*.vendeur' => ['nullable', 'string', 'max:120'],
            'rows.*.site_affecte' => ['nullable', 'string', 'max:120'],
            'rows.*.date_creation_commande' => $date,
            'rows.*.date_affectation' => $date,
            'rows.*.date_arrivage_reelle' => $date,
            'rows.*.date_desaffectation' => $date,
            'rows.*.version' => ['nullable', 'string', 'max:45'],
        ];
    }
}
