<?php

namespace App\DTOs\Depot;

readonly class CreateDepotDto
{
    public function __construct(
        public ?string $name,
        public ?string $type,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) && $data['name'] !== '' ? (string) $data['name'] : null,
            type: isset($data['type']) && $data['type'] !== '' ? (string) $data['type'] : null,
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
