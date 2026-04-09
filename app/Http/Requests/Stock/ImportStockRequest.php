<?php

namespace App\Http\Requests\Stock;

use App\DTOs\Stock\CreateStockDto;
use Illuminate\Foundation\Http\FormRequest;

class ImportStockRequest extends FormRequest{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ];
    }
}