<?php

namespace App\DTOs\Profile;

readonly class CreateProfileDto
{
    public function __construct(
        public string $nom,
        public ?string $libelle,
        public ?string $statut,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nom: (string) $data['nom'],
            libelle: isset($data['libelle']) && $data['libelle'] !== '' ? (string) $data['libelle'] : null,
            statut: isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'nom' => $this->nom,
            'libelle' => $this->libelle,
            'statut' => $this->statut ?? 'actif',
        ], static fn ($v) => $v !== null);
    }
}
