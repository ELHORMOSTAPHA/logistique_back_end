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
            'entree_stock_date' => ['sometimes', 'nullable', 'date'],
            'depot_id' => ['sometimes', 'nullable', 'integer', 'exists:depots,id'],
            'stock_status_id' => ['sometimes', 'nullable', 'integer', 'exists:stock_statuts,id'],
            'statut' => ['sometimes', 'nullable', 'string', 'max:45'],
            'numero_lot' => ['sometimes', 'nullable', 'string', 'max:45'],
            'numero_arrivage' => ['sometimes', 'nullable', 'string', 'max:45'],
            'lot_id' => ['sometimes', 'nullable', 'integer', 'exists:lots,id'],
            'combinaison_rare' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'marque' => 'marque',
            'numero_commande' => 'numéro de commande',
            'modele' => 'modèle',
            'version' => 'version',
            'finition' => 'finition',
            'vin' => 'numéro de châssis (VIN)',
            'color_ex' => 'couleur extérieure',
            'color_ex_code' => 'code couleur extérieure',
            'color_int' => 'couleur intérieure',
            'color_int_code' => 'code couleur intérieure',
            'client' => 'client',
            'type_client' => 'type de client',
            'PGEO' => 'PGEO',
            'options' => 'options',
            'vendeur' => 'vendeur',
            'site_affecte' => 'site affecté',
            'date_creation_commande' => 'date de création de la commande',
            'date_arrivage_prevu' => 'date d’arrivage prévue',
            'date_arrivage_reelle' => 'date d’arrivage réelle',
            'date_affectation' => 'date d’affectation',
            'entree_stock_date' => 'date d’entrée en stock',
            'depot_id' => 'dépôt',
            'stock_status_id' => 'statut stock',
            'statut' => 'statut livraison',
            'numero_lot' => 'numéro de lot',
            'numero_arrivage' => 'numéro d’arrivage',
            'lot_id' => 'lot',
            'combinaison_rare' => 'combinaison rare',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max.string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'boolean' => 'Le champ :attribute doit être vrai ou faux.',
            'integer' => 'Le champ :attribute doit être un nombre entier.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'vin.unique' => 'Ce numéro de châssis (VIN) est déjà attribué à un autre véhicule.',
            'depot_id.exists' => 'Le dépôt sélectionné est invalide ou n’existe plus.',
            'stock_status_id.exists' => 'Le statut stock sélectionné est invalide.',
            'lot_id.exists' => 'Le lot sélectionné est invalide.',
        ];
    }
}
