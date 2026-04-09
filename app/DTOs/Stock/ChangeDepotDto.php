<?php

namespace App\DTOs\Stock;

readonly class ChangeDepotDto
{
    public function __construct(
        public int $depot_id,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            depot_id: (int) $data['depot_id'],
        );
    }
}
