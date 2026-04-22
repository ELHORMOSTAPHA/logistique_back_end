<?php

namespace App\Http\Requests\Livraison;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivraisonHistoriqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', 'string', 'in:en_attente,facturé,livré'],
            'infos'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
