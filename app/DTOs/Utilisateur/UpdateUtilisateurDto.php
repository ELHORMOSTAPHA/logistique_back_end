<?php

namespace App\DTOs\Utilisateur;

use App\Http\Requests\Utilisateur\UpdateUtilisateurRequest;

readonly class UpdateUtilisateurDto
{
    public function __construct(
        public ?string $nom,
        public ?string $prenom,
        public ?string $email,
        public ?string $telephone,
        public ?int $id_profile,
        public ?string $statut,
        public ?string $password,
        public ?string $avatar,
    ) {}

    public static function fromRequest(UpdateUtilisateurRequest $request): self
    {
        return new self(
            nom: $request->validated('nom'),
            prenom: $request->validated('prenom'),
            email: $request->validated('email'),
            telephone: $request->validated('telephone'),
            id_profile: $request->validated('id_profile'),
            statut: $request->validated('statut'),
            password: $request->validated('password'),
            avatar: $request->validated('avatar'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = array_filter([
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'id_profile' => $this->id_profile,
            'statut' => $this->statut,
            'avatar' => $this->avatar,
        ], static fn ($v) => $v !== null);

        if ($this->password !== null && $this->password !== '') {
            $data['password'] = $this->password;
        }

        return $data;
    }
}
