<?php

namespace App\Http\Requests\Utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkUpdateUtilisateurStatusRequest extends FormRequest
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
            'statut' => ['required', 'string', 'in:actif,inactif,suspendu'],
            'select_all' => ['sometimes', 'boolean'],
            'excluded_ids' => ['nullable', 'array'],
            'excluded_ids.*' => ['integer'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
            'filters' => ['nullable', 'array'],
            'filters.keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.nom' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.prenom' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.email' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.statut' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.id_profile' => ['sometimes', 'nullable', 'integer', 'exists:profiles,id'],
            'filters.from' => ['sometimes', 'nullable', 'date'],
            'filters.to' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($this->boolean('select_all')) {
                return;
            }
            $ids = $this->input('ids', []);
            if (! is_array($ids) || $ids === []) {
                $v->errors()->add('ids', 'Sélectionnez au moins un utilisateur ou utilisez « Tous ».');
            }
        });
    }
}
