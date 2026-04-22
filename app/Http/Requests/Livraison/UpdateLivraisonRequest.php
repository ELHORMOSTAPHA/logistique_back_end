<?php

namespace App\Http\Requests\Livraison;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLivraisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client'     => ['sometimes', 'string', 'max:255'],
            'statut'     => ['sometimes', 'string', 'in:en_attente,facturé,livré'],
            'ww'         => ['sometimes', 'nullable', 'string', 'max:50'],
            'n_facture'  => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
