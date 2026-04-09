<?php

namespace App\DTOs\Historique;

readonly class ListHistoriqueDto
{
    public function __construct(
        public ?string $user_id,
        public ?string $action,
        public ?string $table_name,
        public ?int $record_id,
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
            user_id: isset($query['user_id']) && $query['user_id'] !== '' ? (string) $query['user_id'] : null,
            action: isset($query['action']) && $query['action'] !== '' ? (string) $query['action'] : null,
            table_name: isset($query['table_name']) && $query['table_name'] !== '' ? (string) $query['table_name'] : null,
            record_id: isset($query['record_id']) && $query['record_id'] !== '' ? (int) $query['record_id'] : null,
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
