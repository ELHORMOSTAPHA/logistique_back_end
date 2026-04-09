<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_status' => $this->stock_status,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at,
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];
    }
}
