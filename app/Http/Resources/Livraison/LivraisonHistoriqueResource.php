<?php

namespace App\Http\Resources\Livraison;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LivraisonHistoriqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'livraison_id' => $this->livraison_id,
            'statut'       => $this->statut,
            'infos'        => $this->infos,
            'created_by'   => $this->created_by,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'creator'      => $this->whenLoaded('creator', fn() => [
                'id'     => $this->creator->id,
                'nom'    => $this->creator->nom,
                'prenom' => $this->creator->prenom,
            ]),
        ];
    }
}
