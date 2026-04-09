<?php

namespace App\Http\Requests\Stock;

use App\DTOs\Stock\CreateStockDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
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
            'modele' => ['required','nullable', 'string', 'max:45'],
            'version' => ['required','nullable', 'string', 'max:45'],
            'marque' => ['required','nullable', 'string', 'max:45'],
            'vin' => ['nullable', 'string', 'max:45', 'unique:stocks,vin'],
            'color_ex' => ['nullable', 'string', 'max:45'],
            'color_ex_code' => ['nullable', 'string', 'max:45'],
            'color_int' => ['nullable', 'string', 'max:45'],
            'color_int_code' => ['nullable', 'string', 'max:45'],
            'reserved' => ['nullable', 'boolean'],
            'depot_id' => ['nullable', 'integer', 'exists:depots,id'],
            'lot_id' => ['required', 'integer', 'exists:lots,id'],
        ];
    }

    public function toDto(): CreateStockDto
    {
        return CreateStockDto::fromArray($this->validated());
    }
}
