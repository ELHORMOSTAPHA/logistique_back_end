<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkUpdateProfileStatusRequest extends FormRequest
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
            'statut' => ['required', 'string', 'in:actif,inactif'],
            'select_all' => ['sometimes', 'boolean'],
            'excluded_ids' => ['nullable', 'array'],
            'excluded_ids.*' => ['integer'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:profiles,id'],
            'filters' => ['nullable', 'array'],
            'filters.nom' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.libelle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'filters.statut' => ['sometimes', 'nullable', 'string', 'max:255'],
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
                $v->errors()->add('ids', 'Sélectionnez au moins un profil ou utilisez « Tous ».');
            }
        });
    }
}
