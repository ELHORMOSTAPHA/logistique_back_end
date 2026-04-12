<?php

namespace App\Http\Requests\Utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('password') && $this->input('password') === '') {
            $this->merge(['password' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('utilisateur');
        $userId = $user?->id ?? 0;

        return [
            'nom' => ['sometimes', 'nullable', 'string', 'max:255'],
            'prenom' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'telephone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'id_profile' => ['sometimes', 'nullable', 'integer', 'exists:profiles,id'],
            'statut' => ['sometimes', 'nullable', 'string', 'max:255'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
