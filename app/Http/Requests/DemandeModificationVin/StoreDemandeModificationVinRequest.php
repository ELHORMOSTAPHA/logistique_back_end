<?php

namespace App\Http\Requests\DemandeModificationVin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeModificationVinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'demandes_reservation_id' => ['required', 'integer', 'exists:demandes_reservations,id'],
            'stock_id'                => ['required', 'integer', 'exists:stocks,id'],
            'motif'                   => ['required', 'string', 'max:500'],
        ];
    }
}
