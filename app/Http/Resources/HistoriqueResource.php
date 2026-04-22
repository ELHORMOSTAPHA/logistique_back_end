<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoriqueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'table_name' => $this->table_name,
            'record_id' => $this->record_id,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
            'metadata' => $this->metadata,
            'ip_address' => $this->ip_address,
            'http_method' => $this->http_method,
            'request_path' => $this->request_path,
            'user_agent' => $this->user_agent,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
