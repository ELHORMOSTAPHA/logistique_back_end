<?php

namespace App\DTOs\Historique;

use App\Http\Requests\Historique\UpdateHistoriqueRequest;

readonly class UpdateHistoriqueDto
{
    public function __construct(
        public ?string $user_id,
        public ?string $action,
        public ?string $table_name,
        public ?int $record_id,
        public ?string $old_value,
        public ?string $new_value,
    ) {}

    public static function fromRequest(UpdateHistoriqueRequest $request): self
    {
        return new self(
            user_id: $request->validated('user_id'),
            action: $request->validated('action'),
            table_name: $request->validated('table_name'),
            record_id: $request->validated('record_id'),
            old_value: $request->validated('old_value'),
            new_value: $request->validated('new_value'),
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
