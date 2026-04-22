<?php

namespace App\Http\Requests\Depot;

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
            'type_depot_id' => ['nullable', 'integer', 'exists:type_depots,id'],
        ];
    }
}
