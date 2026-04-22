<?php

namespace App\Http\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OldVinInStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vin'         => $this->vin,
            'in_arrivage' => (bool) $this->in_arrivage,
            'expose' => (bool) $this->expose,
        ];
    }
}
