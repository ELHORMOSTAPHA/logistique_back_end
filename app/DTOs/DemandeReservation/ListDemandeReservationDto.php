<?php

namespace App\DTOs\DemandeReservation;

readonly class ListDemandeReservationDto
{
    public function __construct(
        public ?int $stock_id,
        public ?string $statut,
        public ?string $id_demande,
        public ?string $nom_commercial,
        public ?string $keyword,
        public ?string $from,
        public ?string $to,
        public int $per_page,
        public ?int $page,
        public ?string $sort_by,
        public ?string $sort_order,
        public ?bool $paginated,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     */
    public static function fromArray(array $query): self
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return new self(
            stock_id: isset($query['stock_id']) && $query['stock_id'] !== '' ? (int) $query['stock_id'] : null,
            statut: isset($query['statut']) && $query['statut'] !== '' ? (string) $query['statut'] : null,
            id_demande: isset($query['id_demande']) && $query['id_demande'] !== '' ? (string) $query['id_demande'] : null,
            nom_commercial: isset($query['nom_commercial']) && $query['nom_commercial'] !== '' ? (string) $query['nom_commercial'] : null,
            keyword: isset($query['keyword']) && $query['keyword'] !== '' ? (string) $query['keyword'] : null,
            from: isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            to: isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            per_page: $per_page,
            page: isset($query['page']) ? max(1, (int) $query['page']) : null,
            sort_by: isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            sort_order: $sort_order,
            paginated: array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        );
    }
}
