<?php

namespace App\Http\Requests\DemandeModificationVin;

use Illuminate\Foundation\Http\FormRequest;

class RefuserDemandeModificationVinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'motif_refus' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
