<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeModificationVinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'demandes_reservation_id' => $this->demandes_reservation_id,
            'stock_id'                => $this->stock_id,
            'demandeur_id'            => $this->demandeur_id,
            'vin_initial'             => $this->vin_initial,
            'vin_nouveau'             => $this->vin_nouveau,
            'motif'                   => $this->motif,
            'statut'                  => $this->statut,
            'valideur_id'             => $this->valideur_id,
            'validated_at'            => $this->validated_at,
            'motif_refus'             => $this->motif_refus,
            'deleted_by'              => $this->deleted_by,
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
            'deleted_at'              => $this->deleted_at,
            'demande_reservation'     => new DemandeReservationResource($this->whenLoaded('demandeReservation')),
            'stock'                   => new StockResource($this->whenLoaded('stock')),
            'demandeur'               => new UserResource($this->whenLoaded('demandeur')),
            'valideur'                => new UserResource($this->whenLoaded('valideur')),
        ];
    }
}
