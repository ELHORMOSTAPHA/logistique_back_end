<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'id_demande' => $this->id_demande,
            'nom_commercial' => $this->nom_commercial,
            'id_commercial' => $this->id_commercial,
            'demande_infos' => $this->demande_infos,
            'statut' => $this->statut,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'stock' => new StockResource($this->whenLoaded('stock')),
            'demande_changement_vins' => DemandeChangementVinResource::collection($this->whenLoaded('demandeChangementVins')),
        ];
    }
}
