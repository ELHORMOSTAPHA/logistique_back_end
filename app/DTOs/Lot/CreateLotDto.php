<?php

namespace App\DTOs\Lot;

readonly class CreateLotDto
{
    public function __construct(
        public ?string $numero_lot,
        public ?string $numero_arrivage,
        public ?string $statut,
        public ?string $date_arrivage_prevu,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            numero_lot: isset($data['numero_lot']) && $data['numero_lot'] !== '' ? (string) $data['numero_lot'] : null,
            numero_arrivage: isset($data['numero_arrivage']) && $data['numero_arrivage'] !== '' ? (string) $data['numero_arrivage'] : null,
            statut: isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : null,
            date_arrivage_prevu: isset($data['date_arrivage_prevu']) && $data['date_arrivage_prevu'] !== '' ? (string) $data['date_arrivage_prevu'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'numero_lot' => $this->numero_lot,
            'numero_arrivage' => $this->numero_arrivage,
            'statut' => $this->statut,
            'date_arrivage_prevu' => $this->date_arrivage_prevu,
        ], static fn ($v) => $v !== null);
    }
}
