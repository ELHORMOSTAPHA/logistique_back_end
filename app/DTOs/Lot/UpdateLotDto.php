<?php

namespace App\DTOs\Lot;

use App\Http\Requests\Lot\UpdateLotRequest;

readonly class UpdateLotDto
{
    public function __construct(
        public ?string $numero_lot,
        public ?string $numero_arrivage,
        public ?string $statut,
        public ?string $date_arrivage_prevu,
    ) {}

    public static function fromRequest(UpdateLotRequest $request): self
    {
        return new self(
            numero_lot: $request->validated('numero_lot'),
            numero_arrivage: $request->validated('numero_arrivage'),
            statut: $request->validated('statut'),
            date_arrivage_prevu: $request->validated('date_arrivage_prevu'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'numero_lot' => $this->numero_lot,
            'numero_arrivage' => $this->numero_arrivage,
            'statut' => $this->statut,
            'date_arrivage_prevu' => $this->date_arrivage_prevu,
        ], static fn ($v) => $v !== null && $v !== '');

        return $data;
    }
}
