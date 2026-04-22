<?php

namespace App\Http\Requests\Livraison;

use App\Models\Livraison;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLivraisonHistoriqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut'    => ['required', 'string', 'in:en_attente,facturé,livré'],
            'n_facture' => ['nullable', 'string', 'max:100'],
            'ww'        => ['nullable', 'string', 'max:50'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('statut')) {
                    return;
                }

                $statut = $this->input('statut');

                if ($statut === 'facturé' && empty(trim((string) $this->input('n_facture', '')))) {
                    $validator->errors()->add('n_facture', 'Le N° facture est requis lorsque le statut est "facturé".');
                }

                if ($statut === 'livré') {
                    if (empty(trim((string) $this->input('ww', '')))) {
                        $validator->errors()->add('ww', 'Le WW est requis lorsque le statut est "livré".');
                    }

                    $livraisonId = $this->route('id');
                    $livraison   = Livraison::query()->find($livraisonId);

                    if ($livraison && $livraison->statut !== 'facturé') {
                        $validator->errors()->add(
                            'statut',
                            'Le statut "livré" n\'est accessible que lorsque la livraison est déjà "facturée".'
                        );
                    }
                }
            },
        ];
    }
}
