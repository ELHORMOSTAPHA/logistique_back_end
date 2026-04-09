<?php

namespace App\Http\Requests\Stock;

use App\DTOs\Stock\ChangeDepotDto;
use Illuminate\Foundation\Http\FormRequest;

class ChangeDepotStockRequest extends FormRequest
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
            'depot_id' => ['required', 'integer', 'exists:depots,id'],
        ];
    }

    public function toDto(): ChangeDepotDto
    {
        return ChangeDepotDto::fromArray($this->validated());
    }
}
