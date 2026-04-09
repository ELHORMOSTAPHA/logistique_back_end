<?php

namespace App\Http\Requests\DemandeReservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDemandeReservationRequest extends FormRequest
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
            'stock_id' => ['sometimes', 'nullable', 'integer', 'exists:stocks,id'],
            'id_demande' => ['sometimes', 'nullable', 'string', 'max:45'],
            'nom_commercial' => ['sometimes', 'nullable', 'string', 'max:45'],
            'id_commercial' => ['sometimes', 'nullable', 'integer'],
            'demande_infos' => ['sometimes', 'nullable', 'string', 'max:45'],
            'statut' => ['sometimes', 'nullable', 'string', 'max:45'],
        ];
    }
}
