<?php

namespace App\DTOs\Depot;

use App\Http\Requests\Depot\UpdateDepotRequest;

/** Payload for partial depot updates (validated request body only). */
readonly class UpdateDepotDto
{
    public function __construct(
        public ?string $name,
        public ?string $type,
    ) {}

    public static function fromRequest(UpdateDepotRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            type: $request->validated('type'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'type' => $this->type,
        ], static fn ($v) => $v !== null);
    }
}
