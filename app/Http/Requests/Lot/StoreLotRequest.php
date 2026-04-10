<?php

namespace App\Http\Requests\Lot;

use Illuminate\Foundation\Http\FormRequest;

class StoreLotRequest extends FormRequest
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
            'numero_lot' => ['nullable', 'string', 'max:45'],
            'numero_arrivage' => ['nullable', 'string', 'max:45'],
            'statut' => ['nullable', 'string', 'max:45'],
            'date_arrivage_prevu' => ['nullable', 'date'],
        ];
    }
}
