<?php

namespace App\Http\Requests\DemandeReservation;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeReservationRequest extends FormRequest
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
            'stock_id' => ['required', 'integer', 'exists:stocks,id'],
            'id_demande' => ['nullable', 'string', 'max:45'],
            'nom_commercial' => ['nullable', 'string', 'max:45'],
            'id_commercial' => ['nullable', 'integer'],
            'demande_infos' => ['nullable', 'string', 'max:45'],
            'statut' => ['nullable', 'string', 'max:45'],
        ];
    }
}
