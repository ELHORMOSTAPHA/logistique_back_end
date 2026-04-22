<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_depot_id' => $this->type_depot_id,
            'type_depot' => $this->whenLoaded('typeDepot', fn () => [
                'id' => $this->typeDepot?->id,
                'libelle' => $this->typeDepot?->libelle,
            ]),
            'created_by' => $this->created_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
        ];
    }
}
