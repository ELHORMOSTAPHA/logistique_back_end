<?php

namespace App\Http\Resources\Livraison;

use App\Http\Resources\StockResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LivraisonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'stock_id'   => $this->stock_id,
            'client'     => $this->client,
            'statut'     => $this->statut,
            'ww'         => $this->ww,
            'n_facture'  => $this->n_facture,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'stock'      => new StockResource($this->whenLoaded('stock')),
            'creator'    => $this->whenLoaded('creator', fn() => [
                'id'     => $this->creator->id,
                'nom'    => $this->creator->nom,
                'prenom' => $this->creator->prenom,
            ]),
            'updater'    => $this->whenLoaded('updater', fn() => [
                'id'     => $this->updater->id,
                'nom'    => $this->updater->nom,
                'prenom' => $this->updater->prenom,
            ]),
            'livraison_historiques' => LivraisonHistoriqueResource::collection(
                $this->whenLoaded('livraisonHistoriques')
            ),
        ];
    }
}
