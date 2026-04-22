<?php

namespace App\Http\Requests\Livraison;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivraisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stock_id'   => ['required', 'integer', 'exists:stocks,id'],
            'client'     => ['required', 'string', 'max:255'],
            'statut'     => ['nullable', 'string', 'in:en_attente,facturé,livré'],
            'ww'         => ['nullable', 'string', 'max:50'],
            'n_facture'  => ['nullable', 'string', 'max:100'],
        ];
    }
}
