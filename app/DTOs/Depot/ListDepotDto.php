<?php

namespace App\DTOs\Depot;

readonly class ListDepotDto
{
    public function __construct(
        public ?string $name,
        public ?string $type,
        public ?string $created_at,
        public ?string $updated_at,
        public ?string $deleted_at,
        public ?int $created_by,
        public ?int $deleted_by,
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
            name: isset($query['name']) && $query['name'] !== '' ? (string) $query['name'] : null,
            type: isset($query['type']) && $query['type'] !== '' ? (string) $query['type'] : null,
            created_at: isset($query['created_at']) && $query['created_at'] !== '' ? (string) $query['created_at'] : null,
            updated_at: isset($query['updated_at']) && $query['updated_at'] !== '' ? (string) $query['updated_at'] : null,
            deleted_at: isset($query['deleted_at']) && $query['deleted_at'] !== '' ? (string) $query['deleted_at'] : null,
            created_by: isset($query['created_by']) && $query['created_by'] !== '' ? (int) $query['created_by'] : null,
            deleted_by: isset($query['deleted_by']) && $query['deleted_by'] !== '' ? (int) $query['deleted_by'] : null,
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
