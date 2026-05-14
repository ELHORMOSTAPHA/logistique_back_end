<?php

namespace App\Services\CrmSoueast;

use App\Models\CarFinition;
use App\Models\CarMarque;
use App\Models\CarModele;
use App\Models\CrmVehiculeColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Synchronisation des tables référentiel véhicule depuis l'API CRM SOUEAST.
 *
 * Endpoints CRM consommés (préfixe configurable via `crm_soueast.api_prefix`) :
 *  - POST /auth/login        → bearer Sanctum (mis en cache jusqu'à expiration)
 *  - GET  /marques           → upsert dans `car_marques`
 *  - GET  /modeles           → upsert dans `car_modeles`            (FK car_marques)
 *  - GET  /finitions         → upsert dans `car_finitions`          (lié à car_modeles)
 *  - GET  /colors            → upsert dans `crm_vehicules_colors`   (lié à car_modeles)
 *
 * L'ordre des syncs est imposé par la FK `car_modeles.marque_id → car_marques.id`.
 */
class CrmSoueastSyncService
{
    private const TOKEN_CACHE_KEY = 'crm_soueast:token';

    /**
     * Synchronise les 4 référentiels dans le bon ordre.
     *
     * @return array{
     *   marques: array<string,int>,
     *   modeles: array<string,int>,
     *   finitions: array<string,int>,
     *   colors: array<string,int>,
     * }
     */
    public function syncAll(): array
    {
        $token = $this->getToken();

        return [
            'marques'   => $this->syncMarques($token),
            'modeles'   => $this->syncModeles($token),
            'finitions' => $this->syncFinitions($token),
            'colors'    => $this->syncColors($token),
        ];
    }

    /* ------------------------------------------------------------------ *
     *  Authentification
     * ------------------------------------------------------------------ */

    private function getToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }
        return $this->refreshToken();
    }

    private function refreshToken(): string
    {
        $response = Http::acceptJson()
            ->timeout($this->timeout())
            ->post($this->url('/auth/login'), [
                'username' => (string) config('crm_soueast.username'),
                'password' => (string) config('crm_soueast.password'),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                "Échec d'authentification CRM SOUEAST: HTTP {$response->status()} — " . $response->body()
            );
        }

        $token = $response->json('data.token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Réponse de login CRM SOUEAST invalide : token manquant.');
        }

        $ttl = max(1, (int) config('crm_soueast.token_ttl_minutes', 1380));
        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addMinutes($ttl));

        return $token;
    }

    /* ------------------------------------------------------------------ *
     *  Sync par ressource
     * ------------------------------------------------------------------ */

    /**
     * @return array<string,int>
     */
    public function syncMarques(?string $token = null): array
    {
        $token ??= $this->getToken();
        $rows = $this->fetchList($token, '/marques');

        return DB::transaction(function () use ($rows) {
            $c = ['received' => count($rows), 'created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($rows as $row) {
                $id = $this->intOrNull($row['id'] ?? null);
                if ($id === null) {
                    $c['skipped']++;
                    continue;
                }

                $payload = [
                    'name'   => (string) ($row['name'] ?? $row['nom'] ?? ''),
                    'status' => (int) ($row['status'] ?? 1),
                ];

                $this->upsertById(CarMarque::class, $id, $payload, $c);
            }

            return $c;
        });
    }

    /**
     * @return array<string,int>
     */
    public function syncModeles(?string $token = null): array
    {
        $token ??= $this->getToken();
        $rows = $this->fetchList($token, '/modeles');

        return DB::transaction(function () use ($rows) {
            $c = ['received' => count($rows), 'created' => 0, 'updated' => 0, 'skipped' => 0];
            $marqueIds = CarMarque::query()->pluck('id')->flip()->all();

            foreach ($rows as $row) {
                $id        = $this->intOrNull($row['id'] ?? null);
                $marqueId  = $this->intOrNull($row['marque_id'] ?? null);

                if ($id === null || $marqueId === null || ! isset($marqueIds[$marqueId])) {
                    $c['skipped']++;
                    continue;
                }

                $payload = [
                    'name'      => (string) ($row['name'] ?? $row['nom'] ?? ''),
                    'marque_id' => $marqueId,
                    'status'    => (int) ($row['status'] ?? 1),
                ];

                $this->upsertById(CarModele::class, $id, $payload, $c);
            }

            return $c;
        });
    }

    /**
     * @return array<string,int>
     */
    public function syncFinitions(?string $token = null): array
    {
        $token ??= $this->getToken();
        $rows = $this->fetchList($token, '/finitions');

        return DB::transaction(function () use ($rows) {
            $c = ['received' => count($rows), 'created' => 0, 'updated' => 0, 'skipped' => 0];
            $modeleIds = CarModele::query()->pluck('id')->flip()->all();

            foreach ($rows as $row) {
                $id       = $this->intOrNull($row['id'] ?? null);
                $modeleId = $this->intOrNull($row['modele_id'] ?? null);

                if ($id === null || $modeleId === null || ! isset($modeleIds[$modeleId])) {
                    $c['skipped']++;
                    continue;
                }

                $payload = $row;
                unset($payload['id']);
                $payload['modele_id'] = $modeleId;
                if (isset($payload['nom']) && ! isset($payload['name'])) {
                    $payload['name'] = (string) $payload['nom'];
                }
                unset($payload['nom']);

                $this->upsertById(CarFinition::class, $id, $payload, $c);
            }

            return $c;
        });
    }

    /**
     * @return array<string,int>
     */
    public function syncColors(?string $token = null): array
    {
        $token ??= $this->getToken();
        $rows = $this->fetchList($token, '/colors');

        return DB::transaction(function () use ($rows) {
            $c = ['received' => count($rows), 'created' => 0, 'updated' => 0, 'skipped' => 0];
            $modeleIds = CarModele::query()->pluck('id')->flip()->all();

            foreach ($rows as $row) {
                $id       = $this->intOrNull($row['id'] ?? null);
                $modeleId = $this->intOrNull($row['modele_id'] ?? null);

                if ($id === null || $modeleId === null || ! isset($modeleIds[$modeleId])) {
                    $c['skipped']++;
                    continue;
                }

                $payload = [
                    'nom'       => (string) ($row['nom'] ?? $row['name'] ?? ''),
                    'reference' => (string) ($row['reference'] ?? ''),
                    'prix'      => $row['prix'] ?? 0,
                    'modele_id' => $modeleId,
                    'type'      => $this->normalizeColorType($row['type'] ?? null),
                    'hex_color' => (string) ($row['hex_color'] ?? ''),
                ];

                $this->upsertById(CrmVehiculeColor::class, $id, $payload, $c);
            }

            return $c;
        });
    }

    /* ------------------------------------------------------------------ *
     *  Helpers HTTP
     * ------------------------------------------------------------------ */

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchList(string $token, string $path): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout($this->timeout())
            ->retry(2, 250, throw: false)
            ->get($this->url($path));

        // Token expiré côté CRM : on relogue et on retente une fois.
        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $token = $this->refreshToken();
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout($this->timeout())
                ->get($this->url($path));
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                "Échec d'appel CRM SOUEAST {$path}: HTTP {$response->status()} — " . $response->body()
            );
        }

        $data = $response->json('data');
        if (! is_array($data)) {
            return [];
        }

        return array_values($data);
    }

    /* ------------------------------------------------------------------ *
     *  Helpers internes
     * ------------------------------------------------------------------ */

    /**
     * Upsert sur la clé primaire `id`, met à jour les compteurs `$c` (created/updated).
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string,mixed>  $payload
     * @param  array<string,int>    $c
     */
    private function upsertById(string $modelClass, int $id, array $payload, array &$c): void
    {
        /** @var Model|null $existing */
        $existing = $modelClass::find($id);

        if ($existing === null) {
            $payload['id'] = $id;
            $modelClass::query()->create($payload);
            $c['created']++;
            return;
        }

        $existing->fill($payload)->save();
        $c['updated']++;
    }

    private function intOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }
        return null;
    }

    private function normalizeColorType(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = strtolower(trim($value));
        return in_array($value, ['ext', 'int'], true) ? $value : null;
    }

    private function url(string $path): string
    {
        $base   = rtrim((string) config('crm_soueast.base_url'), '/');
        $prefix = trim((string) config('crm_soueast.api_prefix', '/api/v1'), '/');
        $path   = '/' . ltrim($path, '/');

        return $base . ($prefix !== '' ? '/' . $prefix : '') . $path;
    }

    private function timeout(): int
    {
        return max(1, (int) config('crm_soueast.timeout', 30));
    }
}
