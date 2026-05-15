<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation pré-import : on s'assure que la structure des lignes est correcte
 * (champs obligatoires renseignés) avant d'appeler la vérification catalogue
 * (marque / modèle / finition / couleurs dans car_marques, car_modeles,
 * car_finitions, crm_vehicules_colors).
 */
class ValidateImportCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_no' => ['required', 'integer', 'min:1'],
            'lines.*.marque' => ['required', 'string', 'max:100'],
            'lines.*.modele' => ['required', 'string', 'max:100'],
            'lines.*.finition' => ['required', 'string', 'max:100'],
            'lines.*.color_ex' => ['required', 'string', 'max:100'],
            'lines.*.color_int' => ['required', 'string', 'max:100'],

            'lines.*.vin' => ['nullable', 'string', 'max:45'],
            'lines.*.numero_commande' => ['nullable', 'string', 'max:45'],
            'lines.*.numero_lot' => ['nullable', 'string', 'max:45'],
            'lines.*.statut' => ['nullable', 'string', 'max:45'],
            'lines.*.client' => ['nullable', 'string', 'max:120'],
            'lines.*.type_client' => ['nullable', 'string', 'max:45'],
            'lines.*.PGEO' => ['nullable', 'string', 'max:45'],
            'lines.*.options' => ['nullable', 'string'],
            'lines.*.vendeur' => ['nullable', 'string', 'max:120'],
            'lines.*.site_affecte' => ['nullable', 'string', 'max:120'],
            'lines.*.date_creation_commande' => ['nullable', 'date'],
            'lines.*.date_arrivage_prevu' => ['nullable', 'date'],
            'lines.*.date_arrivage_reelle' => ['nullable', 'date'],
            'lines.*.date_affectation' => ['nullable', 'date'],
            'lines.*.date_desaffectation' => ['nullable', 'date'],
            'lines.*.version' => ['nullable', 'string', 'max:45'],
        ];
    }
}
