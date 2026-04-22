<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class PreviewVinUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $date = ['nullable', 'date'];

        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_no' => ['required', 'integer', 'min:1'],
            'lines.*.vin' => ['required', 'string', 'max:45'],
            'lines.*.numero_commande' => ['required', 'string', 'max:45'],
            'lines.*.marque' => ['required', 'string', 'max:45'],
            'lines.*.modele' => ['required', 'string', 'max:45'],
            'lines.*.finition' => ['required', 'string', 'max:45'],
            'lines.*.color_ex' => ['required', 'string', 'max:45'],
            'lines.*.color_int' => ['required', 'string', 'max:45'],
            'lines.*.numero_lot' => ['nullable', 'string', 'max:45'],
            'lines.*.statut' => ['nullable', 'string', 'max:45'],
            'lines.*.date_arrivage_prevu' => $date,
            'lines.*.client' => ['nullable', 'string', 'max:120'],
            'lines.*.type_client' => ['nullable', 'string', 'max:45'],
            'lines.*.PGEO' => ['nullable', 'string', 'max:45'],
            'lines.*.options' => ['nullable', 'string'],
            'lines.*.vendeur' => ['nullable', 'string', 'max:120'],
            'lines.*.site_affecte' => ['nullable', 'string', 'max:120'],
            'lines.*.date_creation_commande' => $date,
            'lines.*.date_affectation' => $date,
            'lines.*.date_arrivage_reelle' => $date,
            'lines.*.date_desaffectation' => $date,
            'lines.*.version' => ['nullable', 'string', 'max:45'],
        ];
    }
}
