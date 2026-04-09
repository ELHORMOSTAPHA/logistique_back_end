<?php

namespace App\Http\Requests\Historique;

use App\DTOs\Historique\CreateHistoriqueDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreHistoriqueRequest extends FormRequest
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
            'user_id' => ['nullable', 'string', 'max:45'],
            'action' => ['nullable', 'string', 'max:45'],
            'table_name' => ['nullable', 'string', 'max:45'],
            'record_id' => ['nullable', 'integer'],
            'old_value' => ['nullable', 'string'],
            'new_value' => ['nullable', 'string'],
        ];
    }

    public function toDto(): CreateHistoriqueDto
    {
        return CreateHistoriqueDto::fromArray($this->validated());
    }
}
