<?php

namespace App\DTOs\Stock;

readonly class ListStockDto
{
    public function __construct(
        public ?string $name,
        public ?string $from,
        public ?string $to,
        public ?string $marque,
        public ?string $modele,
        public ?string $vin,
        public ?bool $reserved,
        public ?int $depot_id,
        public ?int $lot_id,
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
        $reserved = null;
        if (array_key_exists('reserved', $query) && $query['reserved'] !== null && $query['reserved'] !== '') {
            $reserved = filter_var($query['reserved'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($reserved === null) {
                $reserved = (bool) $query['reserved'];
            }
        }

        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return new self(
            name: isset($query['name']) && $query['name'] !== '' ? (string) $query['name'] : null,
            from: isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            to: isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            marque: isset($query['marque']) && $query['marque'] !== '' ? (string) $query['marque'] : null,
            modele: isset($query['modele']) && $query['modele'] !== '' ? (string) $query['modele'] : null,
            vin: isset($query['vin']) && $query['vin'] !== '' ? (string) $query['vin'] : null,
            reserved: $reserved,
            depot_id: isset($query['depot_id']) && $query['depot_id'] !== '' ? (int) $query['depot_id'] : null,
            lot_id: isset($query['lot_id']) && $query['lot_id'] !== '' ? (int) $query['lot_id'] : null,
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
