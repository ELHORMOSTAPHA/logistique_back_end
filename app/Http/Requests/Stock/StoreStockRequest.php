<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
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
            'modele' => ['required','nullable', 'string', 'max:45'],
            'version' => ['required','nullable', 'string', 'max:45'],
            'vin' => ['nullable', 'string', 'max:45', 'unique:stocks,vin'],
            'color_ex' => ['nullable', 'string', 'max:45'],
            'color_ex_code' => ['nullable', 'string', 'max:45'],
            'color_int' => ['nullable', 'string', 'max:45'],
            'color_int_code' => ['nullable', 'string', 'max:45'],
            'reserved' => ['nullable', 'boolean'],
            'depot_id' => ['nullable', 'integer', 'exists:depots,id'],
            'lot_id' => ['required', 'integer', 'exists:lots,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'modele' => 'modèle',
            'version' => 'version',
            'vin' => 'numéro de châssis (VIN)',
            'color_ex' => 'couleur extérieure',
            'color_ex_code' => 'code couleur extérieure',
            'color_int' => 'couleur intérieure',
            'color_int_code' => 'code couleur intérieure',
            'reserved' => 'réservé',
            'depot_id' => 'dépôt',
            'lot_id' => 'lot',
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
            'boolean' => 'Le champ :attribute doit être vrai ou faux.',
            'integer' => 'Le champ :attribute doit être un nombre entier.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'vin.unique' => 'Ce numéro de châssis (VIN) est déjà attribué à un autre véhicule.',
            'depot_id.exists' => 'Le dépôt sélectionné est invalide ou n’existe plus.',
            'lot_id.exists' => 'Le lot sélectionné est invalide.',
        ];
    }
}
