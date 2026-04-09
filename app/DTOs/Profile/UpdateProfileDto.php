<?php

namespace App\DTOs\Profile;

use App\Http\Requests\Profile\UpdateProfileRequest;

readonly class UpdateProfileDto
{
    public function __construct(
        public ?string $nom,
        public ?string $libelle,
        public ?string $statut,
    ) {}

    public static function fromRequest(UpdateProfileRequest $request): self
    {
        return new self(
            nom: $request->validated('nom'),
            libelle: $request->validated('libelle'),
            statut: $request->validated('statut'),
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
            'statut' => $this->statut,
        ], static fn ($v) => $v !== null && $v !== '');
    }
}
