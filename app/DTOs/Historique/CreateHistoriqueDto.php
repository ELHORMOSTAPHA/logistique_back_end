<?php

namespace App\DTOs\Historique;

readonly class CreateHistoriqueDto
{
    public function __construct(
        public ?string $user_id,
        public ?string $action,
        public ?string $table_name,
        public ?int $record_id,
        public ?string $old_value,
        public ?string $new_value,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            user_id: isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            action: isset($data['action']) && $data['action'] !== '' ? (string) $data['action'] : null,
            table_name: isset($data['table_name']) && $data['table_name'] !== '' ? (string) $data['table_name'] : null,
            record_id: isset($data['record_id']) && $data['record_id'] !== '' ? (int) $data['record_id'] : null,
            old_value: isset($data['old_value']) ? (string) $data['old_value'] : null,
            new_value: isset($data['new_value']) ? (string) $data['new_value'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user_id,
            'action' => $this->action,
            'table_name' => $this->table_name,
            'record_id' => $this->record_id,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
        ], static fn ($v) => $v !== null);
    }
}
