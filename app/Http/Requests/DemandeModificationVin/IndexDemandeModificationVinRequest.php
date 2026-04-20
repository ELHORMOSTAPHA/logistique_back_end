<?php

namespace App\Http\Requests\DemandeModificationVin;

use Illuminate\Foundation\Http\FormRequest;

class IndexDemandeModificationVinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'statut'                  => ['nullable', 'string', 'max:45'],
            'demandes_reservation_id' => ['nullable', 'integer'],
            'demandeur_id'            => ['nullable', 'integer'],
            'paginated'               => ['nullable', 'boolean'],
            'page'                    => ['nullable', 'integer', 'min:1'],
            'per_page'                => ['nullable', 'integer', 'min:1', 'max:200'],
            'sort_by'                 => ['nullable', 'string', 'max:45'],
            'sort_order'              => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
