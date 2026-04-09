<?php

namespace App\DTOs\DemandeReservation;

readonly class CreateDemandeReservationDto
{
    public function __construct(
        public int $stock_id,
        public ?string $id_demande,
        public ?string $nom_commercial,
        public ?int $id_commercial,
        public ?string $demande_infos,
        public ?string $statut,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            stock_id: (int) $data['stock_id'],
            id_demande: isset($data['id_demande']) && $data['id_demande'] !== '' ? (string) $data['id_demande'] : null,
            nom_commercial: isset($data['nom_commercial']) && $data['nom_commercial'] !== '' ? (string) $data['nom_commercial'] : null,
            id_commercial: isset($data['id_commercial']) && $data['id_commercial'] !== '' ? (int) $data['id_commercial'] : null,
            demande_infos: isset($data['demande_infos']) && $data['demande_infos'] !== '' ? (string) $data['demande_infos'] : null,
            statut: isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'stock_id' => $this->stock_id,
            'id_demande' => $this->id_demande,
            'nom_commercial' => $this->nom_commercial,
            'id_commercial' => $this->id_commercial,
            'demande_infos' => $this->demande_infos,
        ];
        if ($this->statut !== null) {
            $data['statut'] = $this->statut;
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
