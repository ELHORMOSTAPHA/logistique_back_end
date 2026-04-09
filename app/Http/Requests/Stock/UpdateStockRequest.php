<?php

namespace App\Http\Requests\Stock;

use App\DTOs\Stock\UpdateStockDto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStockRequest extends FormRequest
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
        $id = $this->route('stock')?->id ?? $this->route('stock');

        return [
            'modele' => ['sometimes', 'nullable', 'string', 'max:45'],
            'version' => ['sometimes', 'nullable', 'string', 'max:45'],
            'marque' => ['sometimes', 'nullable', 'string', 'max:45'],
            'vin' => ['sometimes', 'nullable', 'string', 'max:45','unique:stocks,vin,'.$id],
            'color_ex' => ['sometimes', 'nullable', 'string', 'max:45'],
            'color_ex_code' => ['sometimes', 'nullable', 'string', 'max:45'],
            'color_int' => ['sometimes', 'nullable', 'string', 'max:45'],
            'color_int_code' => ['sometimes', 'nullable', 'string', 'max:45'],
            'reserved' => ['sometimes', 'nullable', 'boolean'],
            'depot_id' => ['sometimes', 'nullable', 'integer', 'exists:depots,id'],
            'lot_id' => ['sometimes', 'required', 'integer', 'exists:lots,id'],
        ];
    }

    public function toDto(): UpdateStockDto
    {
        return UpdateStockDto::fromRequest($this);
    }
}
