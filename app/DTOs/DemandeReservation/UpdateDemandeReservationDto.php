<?php

namespace App\DTOs\DemandeReservation;

use App\Http\Requests\DemandeReservation\UpdateDemandeReservationRequest;

readonly class UpdateDemandeReservationDto
{
    public function __construct(
        public ?int $stock_id,
        public ?string $id_demande,
        public ?string $nom_commercial,
        public ?int $id_commercial,
        public ?string $demande_infos,
        public ?string $statut,
    ) {}

    public static function fromRequest(UpdateDemandeReservationRequest $request): self
    {
        return new self(
            stock_id: $request->validated('stock_id'),
            id_demande: $request->validated('id_demande'),
            nom_commercial: $request->validated('nom_commercial'),
            id_commercial: $request->validated('id_commercial'),
            demande_infos: $request->validated('demande_infos'),
            statut: $request->validated('statut'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'stock_id' => $this->stock_id,
            'id_demande' => $this->id_demande,
            'nom_commercial' => $this->nom_commercial,
            'id_commercial' => $this->id_commercial,
            'demande_infos' => $this->demande_infos,
            'statut' => $this->statut,
        ], static fn ($v) => $v !== null);
    }
}
