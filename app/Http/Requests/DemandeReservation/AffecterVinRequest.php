<?php

namespace App\Http\Requests\DemandeReservation;

use Illuminate\Foundation\Http\FormRequest;

class AffecterVinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'stock_id' => ['required', 'integer', 'exists:stocks,id'],
        ];
    }
}
