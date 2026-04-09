<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Normalizes Laravel paginators into a small JSON-friendly shape (no links / *_url / path).
 * Use from any service or resource that returns paginated API data.
 */
final class PaginationPayload
{
    /**
     * @return array{
     *     current_page: int,
     *     data: array<int, mixed>,
     *     last_page: int,
     *     per_page: int,
     *     total: int,
     *     from: int|null,
     *     to: int|null
     * }
     */
    public static function fromPaginator(LengthAwarePaginator $pagination): array
    {
        return [
            'pagination' => [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
                'has_more_pages' => $pagination->hasMorePages(),
            ],
            'data' => $pagination->items(),
        ];
    }
}
