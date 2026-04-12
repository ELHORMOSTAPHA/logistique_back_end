<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'statut' => $this->statut,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'profile_id' => $this->id_profile,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'module_access' => $this->when(
                $this->relationLoaded('permissions'),
                fn () => $this->permissions
                    ->filter(fn ($p) => $p->relationLoaded('module') && $p->module)
                    ->sortBy(fn ($p) => $p->module->position)
                    ->values()
                    ->map(fn ($p) => [
                        'module_id' => $p->module_id,
                        'key' => $p->module->name,
                        'label' => $p->module->label,
                        'url' => $p->module->url,
                        'can_read' => $p->can_read,
                        'can_create' => $p->can_create,
                        'can_update' => $p->can_update,
                        'can_delete' => $p->can_delete,
                    ])
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
