<?php

namespace App\Services\Historique;

use App\Http\Resources\HistoriqueResource;
use App\Models\Historique;
use App\Models\User;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class HistoriqueService
{
    private const AUDIT_TEXT_MAX = 60000;

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|Collection<int, array<string, mixed>>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::historique($query);
        $builder = Historique::query();

        if ($f['user_id'] !== null) {
            $builder->where('user_id', $f['user_id']);
        }
        if ($f['action'] !== null) {
            $builder->where('action', 'like', '%'.addcslashes($f['action'], '%_\\').'%');
        }
        if ($f['table_name'] !== null) {
            $builder->where('table_name', 'like', '%'.addcslashes($f['table_name'], '%_\\').'%');
        }
        if ($f['record_id'] !== null) {
            $builder->where('record_id', $f['record_id']);
        }
        if ($f['keyword'] !== null) {
            $like = '%'.addcslashes($f['keyword'], '%_\\').'%';
            $builder->where(function ($q) use ($like) {
                $q->where('old_value', 'like', $like)
                    ->orWhere('new_value', 'like', $like)
                    ->orWhere('table_name', 'like', $like)
                    ->orWhere('action', 'like', $like)
                    ->orWhere('user_id', 'like', $like)
                    ->orWhere('request_path', 'like', $like)
                    ->orWhere('ip_address', 'like', $like);
            });
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $start = Carbon::parse($f['from'])->startOfDay();
            $end = Carbon::parse($f['to'])->endOfDay();
            $builder->whereBetween('created_at', [$start, $end]);
        }

        $allowedSort = ['id', 'created_at', 'action', 'table_name'];
        $sortBy = in_array($f['sort_by'], $allowedSort, true) ? $f['sort_by'] : 'id';
        $order = in_array($f['sort_order'], ['asc', 'desc'], true) ? $f['sort_order'] : 'desc';
        $builder->orderBy($sortBy, $order);

        if ($f['paginated'] === false) {
            $rows = $builder->get();
            $map = $this->userSummaryMapForHistoriques($rows);

            return $rows->map(fn (Historique $h) => $this->historiqueToApiArray($h, $map))->values();
        }

        $pagination = $builder->paginate($f['per_page'], ['*'], 'page', $f['page'] ?? 1);
        $collection = $pagination->getCollection();
        $map = $this->userSummaryMapForHistoriques($collection);
        $pagination->setCollection(
            $collection->map(fn (Historique $h) => $this->historiqueToApiArray($h, $map)),
        );

        return PaginationPayload::fromPaginator($pagination);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentForApi(Historique $historique): array
    {
        $map = $this->userSummaryMapForHistoriques(collect([$historique]));

        return $this->historiqueToApiArray($historique, $map);
    }

    /**
     * @param  Collection<int, Historique>  $rows
     * @return array<int, array{id: int, nom: ?string, prenom: ?string}>
     */
    private function userSummaryMapForHistoriques(Collection $rows): array
    {
        $ids = [];
        foreach ($rows as $h) {
            if (! $h instanceof Historique) {
                continue;
            }
            if ($h->created_by) {
                $ids[(int) $h->created_by] = true;
            }
            $actorId = $this->parseNumericUserId($h->user_id);
            if ($actorId !== null) {
                $ids[$actorId] = true;
            }
        }
        if ($ids === []) {
            return [];
        }

        return User::query()
            ->whereIn('id', array_keys($ids))
            ->get(['id', 'nom', 'prenom'])
            ->keyBy('id')
            ->map(static fn (User $u) => [
                'id' => (int) $u->id,
                'nom' => $u->nom,
                'prenom' => $u->prenom,
            ])
            ->all();
    }

    /**
     * @param  array<int, array{id: int, nom: ?string, prenom: ?string}>  $userMap
     * @return array<string, mixed>
     */
    private function historiqueToApiArray(Historique $historique, array $userMap): array
    {
        $base = (new HistoriqueResource($historique))->toArray(request());
        $actorId = $this->parseNumericUserId($historique->user_id);
        $base['user_actor'] = $actorId !== null && isset($userMap[$actorId])
            ? $userMap[$actorId]
            : null;
        $createdBy = $historique->created_by !== null ? (int) $historique->created_by : null;
        $base['created_by_user'] = $createdBy !== null && isset($userMap[$createdBy])
            ? $userMap[$createdBy]
            : null;

        return $base;
    }

    private function parseNumericUserId(?string $userId): ?int
    {
        if ($userId === null || $userId === '') {
            return null;
        }
        if (ctype_digit((string) $userId)) {
            return (int) $userId;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $userId): Historique
    {
        $attributes = array_filter([
            'user_id' => isset($data['user_id']) && $data['user_id'] !== '' ? (string) $data['user_id'] : null,
            'action' => isset($data['action']) && $data['action'] !== '' ? (string) $data['action'] : null,
            'table_name' => isset($data['table_name']) && $data['table_name'] !== '' ? (string) $data['table_name'] : null,
            'record_id' => isset($data['record_id']) && $data['record_id'] !== '' ? (int) $data['record_id'] : null,
            'old_value' => isset($data['old_value']) ? (string) $data['old_value'] : null,
            'new_value' => isset($data['new_value']) ? (string) $data['new_value'] : null,
            'metadata' => isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : null,
            'ip_address' => isset($data['ip_address']) && $data['ip_address'] !== '' ? (string) $data['ip_address'] : null,
            'http_method' => isset($data['http_method']) && $data['http_method'] !== '' ? (string) $data['http_method'] : null,
            'request_path' => isset($data['request_path']) && $data['request_path'] !== '' ? (string) $data['request_path'] : null,
            'user_agent' => isset($data['user_agent']) && $data['user_agent'] !== '' ? (string) $data['user_agent'] : null,
        ], static fn ($v) => $v !== null);
        $attributes['created_by'] = $userId;
        $attributes['created_at'] = Date::now();

        return Historique::query()->create($attributes);
    }

    /**
     * Central traceability entry point for controllers and services.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function logAction(
        ?Request $request,
        string $action,
        ?string $tableName = null,
        ?int $recordId = null,
        mixed $oldValue = null,
        mixed $newValue = null,
        ?array $metadata = null,
        ?int $actorUserId = null,
    ): void {
        try {
            $actorId = $actorUserId ?? Auth::id();
            $userIdString = $this->resolveUserIdString($request, $actorId);

            $row = [
                'user_id' => $userIdString,
                'action' => $this->truncate($action, 45),
                'table_name' => $tableName !== null ? $this->truncate($tableName, 45) : null,
                'record_id' => $recordId,
                'old_value' => $this->serializeAuditFragment($oldValue),
                'new_value' => $this->serializeAuditFragment($newValue),
                'metadata' => $metadata,
                'ip_address' => $request?->ip() !== null ? $this->truncate((string) $request->ip(), 64) : null,
                'http_method' => $request !== null ? $this->truncate($request->method(), 12) : null,
                'request_path' => $request !== null ? $this->truncate('/'.ltrim($request->path(), '/'), 512) : null,
                'user_agent' => $request?->userAgent() !== null
                    ? $this->truncate((string) $request->userAgent(), self::AUDIT_TEXT_MAX)
                    : null,
            ];

            $this->create($row, $actorId);
        } catch (\Throwable $e) {
            Log::warning('HistoriqueService::logAction failed', [
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function resolveUserIdString(?Request $request, ?int $actorId): ?string
    {
        if ($actorId !== null) {
            return (string) $actorId;
        }

        $client = $request?->attributes->get('integration_client');
        if ($client !== null) {
            return 'int:'.(int) $client->id;
        }

        return null;
    }

    private function serializeAuditFragment(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($encoded === false) {
                $encoded = '[unserializable]';
            }
        } else {
            $encoded = (string) $value;
        }

        return $this->truncate($encoded, self::AUDIT_TEXT_MAX);
    }

    private function truncate(string $value, int $max): string
    {
        if (strlen($value) <= $max) {
            return $value;
        }

        return substr($value, 0, $max - 3).'...';
    }

    public function find(int $id): ?Historique
    {
        return Historique::query()->find($id);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(int $id, array $validated): ?Historique
    {
        $row = Historique::query()->find($id);
        if (! $row) {
            return null;
        }

        $data = array_filter([
            'user_id' => $validated['user_id'] ?? null,
            'action' => $validated['action'] ?? null,
            'table_name' => $validated['table_name'] ?? null,
            'record_id' => $validated['record_id'] ?? null,
            'old_value' => $validated['old_value'] ?? null,
            'new_value' => $validated['new_value'] ?? null,
            'metadata' => isset($validated['metadata']) && is_array($validated['metadata']) ? $validated['metadata'] : null,
            'ip_address' => $validated['ip_address'] ?? null,
            'http_method' => $validated['http_method'] ?? null,
            'request_path' => $validated['request_path'] ?? null,
            'user_agent' => $validated['user_agent'] ?? null,
        ], static fn ($v) => $v !== null);
        if ($data !== []) {
            $row->update($data);
        }

        return $row->fresh();
    }

    public function delete(int $id): bool
    {
        $row = Historique::query()->find($id);
        if (! $row) {
            return false;
        }

        return (bool) $row->delete();
    }
}
