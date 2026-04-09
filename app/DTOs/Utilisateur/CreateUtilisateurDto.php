<?php

namespace App\DTOs\Utilisateur;

readonly class CreateUtilisateurDto
{
    public function __construct(
        public string $nom,
        public string $prenom,
        public string $email,
        public ?string $telephone,
        public ?int $id_profile,
        public string $statut,
        public string $password,
        public ?string $avatar,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nom: (string) $data['nom'],
            prenom: (string) $data['prenom'],
            email: (string) $data['email'],
            telephone: isset($data['telephone']) && $data['telephone'] !== '' ? (string) $data['telephone'] : null,
            id_profile: isset($data['id_profile']) && $data['id_profile'] !== '' ? (int) $data['id_profile'] : null,
            statut: isset($data['statut']) && $data['statut'] !== '' ? (string) $data['statut'] : 'actif',
            password: (string) $data['password'],
            avatar: isset($data['avatar']) && $data['avatar'] !== '' ? (string) $data['avatar'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'id_profile' => $this->id_profile,
            'statut' => $this->statut,
            'password' => $this->password,
            'avatar' => $this->avatar,
        ], static fn ($v) => $v !== null);
    }
}
