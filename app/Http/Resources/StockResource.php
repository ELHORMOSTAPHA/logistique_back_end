<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'marque' => $this->marque,
            'modele' => $this->modele,
            /** @deprecated Utiliser `finition` — alias pour anciens clients. */
            'version' => $this->finition,
            'vin' => $this->vin,
            'numero_commande' => $this->numero_commande,
            'client' => $this->client,
            'type_client' => $this->type_client,
            'PGEO' => $this->PGEO,
            'finition' => $this->finition,
            'expose' => $this->expose,
            // Ne jamais exposer une date d’exposition si le véhicule n’est plus marqué « exposé ».
            'expose_date' => (int) ($this->expose ?? 0) === 1 ? $this->expose_date : null,
            'color_ex' => $this->color_ex,
            'color_ex_code' => $this->color_ex_code,
            'color_int' => $this->color_int,
            'color_int_code' => $this->color_int_code,
            'options' => $this->options,
            'vendeur' => $this->vendeur,
            'site_affecte' => $this->site_affecte,
            'date_creation_commande' => $this->date_creation_commande,
            'reserved' => $this->reserved,
            'depot_id' => $this->depot_id,
            'stock_status_id' => $this->stock_status_id,
            'entree_stock_date' => $this->entree_stock_date,
            'date_arrivage_prevu' => $this->date_arrivage_prevu,
            'date_arrivage_reelle' => $this->date_arrivage_reelle,
            'date_affectation' => $this->date_affectation,
            'numero_lot' => $this->numero_lot,
            'numero_arrivage' => $this->numero_arrivage,
            'etat_avancement' => $this->etat_avancement,
            'statut' => $this->statut,
            'combinaison_rare' => (bool) $this->combinaison_rare,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'depot' => new DepotResource($this->whenLoaded('depot')),
            'stock_status' => new StockStatusResource($this->whenLoaded('stockStatus')),
            'created_by_user' => $this->whenLoaded('createdByUser', fn () => [
                'id' => $this->createdByUser?->id,
                'nom' => $this->createdByUser?->nom,
                'prenom' => $this->createdByUser?->prenom,
            ]),
            'livraison'=>$this->livraison,
            'updated_by_user' => $this->whenLoaded('updatedByUser', fn () => [
                'id' => $this->updatedByUser?->id,
                'nom' => $this->updatedByUser?->nom,
                'prenom' => $this->updatedByUser?->prenom,
            ]),
        ];
    }
}
