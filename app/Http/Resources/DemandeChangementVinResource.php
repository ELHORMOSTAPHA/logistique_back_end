<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeChangementVinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'demandeur' => $this->demandeur,
            'valideur' => $this->valideur,
            'motif' => $this->motif,
            'vin_remplace' => $this->vin_remplace,
            'statut' => $this->statut,
            'demandes_reservation_id' => $this->demandes_reservation_id,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'demande_reservation' => new DemandeReservationResource($this->whenLoaded('demandeReservation')),
        ];
    }
}
