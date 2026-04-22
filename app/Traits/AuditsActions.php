<?php

namespace App\Traits;

use App\Services\Historique\HistoriqueService;

trait AuditsActions
{
    /**
     * Persist a global audit row (never throws — failures are logged only).
     *
     * @param  array<string, mixed>|null  $metadata
     */
    protected function audit(
        string $action,
        ?string $tableName = null,
        ?int $recordId = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?array $metadata = null,
        ?int $actorUserId = null,
    ): void {
        app(HistoriqueService::class)->logAction(
            request(),
            $action,
            $tableName,
            $recordId,
            $oldValue,
            $newValue,
            $metadata,
            $actorUserId,
        );
    }
}
