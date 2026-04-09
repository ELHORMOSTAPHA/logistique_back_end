<?php

namespace App\Http\Requests\Depot;

use App\DTOs\Depot\CreateDepotDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepotRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:45'],
            'type' => ['nullable', 'string', 'max:45'],
        ];
    }

    public function toDto(): CreateDepotDto
    {
        return CreateDepotDto::fromArray($this->validated());
    }
}
