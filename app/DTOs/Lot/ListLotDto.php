<?php

namespace App\DTOs\Lot;

readonly class ListLotDto
{
    public function __construct(
        public ?string $numero_lot,
        public ?string $numero_arrivage,
        public ?string $statut,
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
            numero_lot: isset($query['numero_lot']) && $query['numero_lot'] !== '' ? (string) $query['numero_lot'] : null,
            numero_arrivage: isset($query['numero_arrivage']) && $query['numero_arrivage'] !== '' ? (string) $query['numero_arrivage'] : null,
            statut: isset($query['statut']) && $query['statut'] !== '' ? (string) $query['statut'] : null,
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
