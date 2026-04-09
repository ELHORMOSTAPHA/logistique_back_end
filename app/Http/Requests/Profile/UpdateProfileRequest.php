<?php

namespace App\Http\Requests\Profile;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        /** @var Profile $profile */
        $profile = $this->route('profile');

        return [
            'nom' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('profiles', 'nom')->ignore($profile->id)],
            'libelle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'statut' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
