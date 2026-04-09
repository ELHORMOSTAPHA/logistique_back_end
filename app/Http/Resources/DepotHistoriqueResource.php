<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepotHistoriqueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'stock_id' => $this->stock_id,
            'depot_id' => $this->depot_id,
            'created_at' => $this->created_at,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at,
            'stock' => new StockResource($this->whenLoaded('stock')),
            'depot' => new DepotResource($this->whenLoaded('depot')),
        ];
    }
}
