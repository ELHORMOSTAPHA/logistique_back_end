<?php

namespace App\Http\Requests\Lot;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLotRequest extends FormRequest
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
            'numero_lot' => ['sometimes', 'nullable', 'string', 'max:45'],
            'numero_arrivage' => ['sometimes', 'nullable', 'string', 'max:45'],
            'statut' => ['sometimes', 'nullable', 'string', 'max:45'],
            'date_arrivage_prevu' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
