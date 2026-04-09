<?php

namespace App\Http\Requests\Utilisateur;

use App\DTOs\Utilisateur\CreateUtilisateurDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreUtilisateurRequest extends FormRequest
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
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'id_profile' => ['nullable', 'integer', 'exists:profiles,id'],
            'statut' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toDto(): CreateUtilisateurDto
    {
        return CreateUtilisateurDto::fromArray($this->validated());
    }
}
