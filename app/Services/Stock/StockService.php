<?php

namespace App\Services\Stock;

use App\Models\DepotHistorique;
use App\Models\Depot;
use App\Models\Stock;
use App\Models\TypeDepot;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockService
{
    /** Dépôt par défaut pour l'import « Alimenter stock » (ligne sans `depot_id` exploitable). */
    private const DEFAULT_STOCK_FEED_DEPOT_ID = 1;
    /** Statut par défaut appliqué aux nouvelles lignes de stock. */
    private const DEFAULT_STOCK_STATUS_ID = 1;
    /** Dépôts `type_depot_id` = entrepôt (entrée stock) : statut + `entree_stock_date` (1ère fois). */
    private const ENTREE_STOCK_TYPE_DEPOT_ID = 1;
    private const ENTREE_STOCK_STATUS_ID = 4;
    private static bool $showroomTypeDepotIdResolved = false;

    /** @var int|null Primary key in `type_depots` for libellé « Showroom » (cached per request). */
    private static ?int $showroomTypeDepotId = null;

    /**
     * Trace un passage du véhicule dans un dépôt lorsque `depot_id` change (ou première affectation).
     */
    private function recordStockDepotHistorique(int $stockId, ?int $previousDepotId, ?int $newDepotId, ?int $userId): void
    {
        if ($newDepotId === null || $newDepotId < 1) {
            return;
        }
        if ($previousDepotId !== null && (int) $previousDepotId === (int) $newDepotId) {
            return;
        }

        // Insertion SQL explicite : heure réelle (évite toute troncature côté cast).
        // La colonne doit être DATETIME (migration 2026_04_19_120000) — si elle reste en DATE, MySQL ne garde que le jour → 00:00:00.
        // Always store DB timestamps in UTC.
        DB::table('depot_historiques')->insert([
            'stock_id' => $stockId,
            'depot_id' => $newDepotId,
            'created_by' => $userId,
            'created_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Résout l’id du type « Showroom » en base (ne pas se fier à un id numérique fixe).
     */
    private function resolveShowroomTypeDepotId(): ?int
    {
        if (self::$showroomTypeDepotIdResolved) {
            return self::$showroomTypeDepotId;
        }

        $id = TypeDepot::query()->where('libelle', 'Showroom')->value('id');
        self::$showroomTypeDepotId = $id !== null ? (int) $id : null;
        self::$showroomTypeDepotIdResolved = true;

        return self::$showroomTypeDepotId;
    }

    private function isShowroomDepot(?int $depotId): bool
    {
        if ($depotId === null || $depotId < 1) {
            return false;
        }

        $showroomTypeId = $this->resolveShowroomTypeDepotId();
        if ($showroomTypeId === null) {
            return false;
        }

        $typeDepotId = Depot::query()->whereKey($depotId)->value('type_depot_id');

        return $typeDepotId !== null && (int) $typeDepotId === $showroomTypeId;
    }

    private function isEntreeStockDepot(?int $depotId): bool
    {
        if ($depotId === null || $depotId < 1) {
            return false;
        }

        $typeId = Depot::query()->whereKey($depotId)->value('type_depot_id');

        return $typeId !== null && (int) $typeId === self::ENTREE_STOCK_TYPE_DEPOT_ID;
    }

    /**
     * Affectation à un dépôt type entrepôt (entrée stock) : `stock_status_id` imposé ;
     * `entree_stock_date` rempli une seule fois (tant qu’il est encore null en base).
     *
     * @return array<string, mixed>
     */
    private function entreeStockDepotPayload(?int $newDepotId, ?string $currentEntreeStockDate = null): array
    {
        if (! $this->isEntreeStockDepot($newDepotId)) {
            return [];
        }

        $out = [
            'stock_status_id' => self::ENTREE_STOCK_STATUS_ID,
        ];
        if ($this->isBlankExposeDateString($currentEntreeStockDate)) {
            $out['entree_stock_date'] = $this->nowStringForExposeDateColumn();
        }

        return $out;
    }

    /**
     * Règle exposition showroom :
     * - Dépôt showroom : `expose` = 1 ; `expose_date` une seule fois (premier passage) puis inchangé aux passages suivants.
     * - Dépôt autre : ne rien modifier sur `expose` / `expose_date` — on garde la trace qu’un passage showroom a déjà eu lieu
     *   (utile si le véhicule a quitté le showroom).
     *
     * @param  string|null  $currentExposeDate  Valeur actuelle (chaîne BDD) ; null ou vide = on pose la date « maintenant ».
     * @return array<string, mixed>
     */
    private function showroomExposurePayload(bool $isShowroomDepot, ?string $currentExposeDate = null): array
    {
        if (! $isShowroomDepot) {
            return [];
        }

        $out = [
            'expose' => 1,
        ];
        if ($this->isBlankExposeDateString($currentExposeDate)) {
            $out['expose_date'] = $this->nowStringForExposeDateColumn();
        }

        return $out;
    }

    private function isBlankExposeDateString(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return false;
    }

    private function asNullableExposeDateString(mixed $value): ?string
    {
        if ($this->isBlankExposeDateString($value)) {
            return null;
        }

        return is_string($value) ? $value : (string) $value;
    }

    /**
     * Heure « métier » pour `stocks.expose_date` (voir `config('app.expose_date_timezone')`, pas APP_TIMEZONE).
     */
    private function nowStringForExposeDateColumn(): string
    {
        return Carbon::now((string) config('app.expose_date_timezone', 'Africa/Casablanca'))->format('Y-m-d H:i:s');
    }

    /**
     * Mise à jour masse : dépôt showroom avec conservation de `expose_date` existant, sinon `COALESCE(expose_date, now)`.
     */
    private function bulkUpdateDepotWithShowroomExposure(Builder $queryBuilder, int $depotId, ?int $userId): int
    {
        $now = $this->nowStringForExposeDateColumn();
        $nowLiteral = DB::connection()->getPdo()->quote($now);

        return (int) $queryBuilder->update([
            'depot_id' => $depotId,
            'updated_by' => $userId,
            'expose' => 1,
            'expose_date' => DB::raw("COALESCE(expose_date, {$nowLiteral})"),
        ]);
    }

    /**
     * Mise à jour masse : dépôt « entrée stock » (type 3) + statut 4, date seulement si encore null.
     */
    private function bulkUpdateDepotWithEntreeStock(Builder $queryBuilder, int $depotId, ?int $userId): int
    {
        $now = $this->nowStringForExposeDateColumn();
        $nowLiteral = DB::connection()->getPdo()->quote($now);

        return (int) $queryBuilder->update([
            'depot_id' => $depotId,
            'updated_by' => $userId,
            'stock_status_id' => self::ENTREE_STOCK_STATUS_ID,
            'entree_stock_date' => DB::raw("COALESCE(entree_stock_date, {$nowLiteral})"),
        ]);
    }

    /**
     * @param  array<string, mixed>  $query  Validated index / query parameters
     * @return array<string, mixed>|Collection<int, Stock>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::stock($query);
        $queryBuilder = Stock::query()->with([
            'depot.typeDepot',
            'stockStatus',
            'createdByUser',
            'updatedByUser',
        ]);
        $this->applyStockListFilters($queryBuilder, $f);
        $allowedSort = ['created_at', 'modele', 'vin', 'id'];
        $sortBy = in_array($f['sort_by'], $allowedSort, true) ? $f['sort_by'] : 'created_at';
        $order = in_array($f['sort_order'], ['asc', 'desc'], true) ? $f['sort_order'] : 'desc';
        $queryBuilder->orderBy($sortBy, $order);

        if ($f['paginated'] === false) {
            return $queryBuilder->get();
        }

        $pagination = $queryBuilder->paginate($f['per_page'], ['*'], 'page', $f['page'] ?? 1);

        return PaginationPayload::fromPaginator($pagination);
    }

    /**
     * Filtres identiques à la liste stock (sans tri / pagination).
     *
     * @param  array<string, mixed>  $f  Sortie de {@see QueryFilterNormalizer::stock()}
     */
    private function applyStockListFilters(Builder $queryBuilder, array $f): void
    {
        if ($f['name'] !== null) {
            $queryBuilder->filterByName($f['name']);
        }
        if ($f['from'] !== null && $f['to'] !== null) {
            $queryBuilder->filterByDate($f['from'], $f['to']);
        }
        if ($f['modele'] !== null) {
            $queryBuilder->filterByModele($f['modele']);
        }
        if ($f['vin'] !== null) {
            $queryBuilder->where('vin', $f['vin']);
        }
        if ($f['reserved'] !== null) {
            $queryBuilder->filterByReserved($f['reserved']);
        }
        if ($f['depot_id'] !== null) {
            $queryBuilder->filterByDepotId($f['depot_id']);
        }
        if ($f['lot_id'] !== null) {
            $queryBuilder->filterByLotId($f['lot_id']);
        }
    }

    /**
     * Met à jour le n° de lot sur les stocks sélectionnés (saisie manuelle, même valeur pour tous).
     * Chaîne vide ou null efface le n° de lot.
     *
     * @param  array<string, mixed>  $data  Payload validé {@see BulkAssignLotStockRequest}
     */
    public function bulkAssignNumeroLot(array $data, ?int $userId): int
    {
        $raw = $data['numero_lot'] ?? null;
        $numeroLot = null;
        if (is_string($raw)) {
            $t = trim($raw);
            $numeroLot = $t === '' ? null : mb_substr($t, 0, 45);
        }

        if (! empty($data['select_all'])) {
            $filters = is_array($data['filters'] ?? null) ? $data['filters'] : [];
            $f = QueryFilterNormalizer::stock($filters);
            $queryBuilder = Stock::query();
            $this->applyStockListFilters($queryBuilder, $f);
            if (! empty($data['excluded_ids']) && is_array($data['excluded_ids'])) {
                $queryBuilder->whereNotIn('id', array_map('intval', $data['excluded_ids']));
            }

            return $queryBuilder->update([
                'numero_lot' => $numeroLot,
                'updated_by' => $userId,
            ]);
        }

        $ids = array_map('intval', $data['ids'] ?? []);
        if ($ids === []) {
            return 0;
        }

        return Stock::query()->whereIn('id', $ids)->update([
            'numero_lot' => $numeroLot,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Change le dépôt pour plusieurs stocks (même dépôt pour tous).
     * L’utilisateur authentifié sert pour `updated_by` et la traçabilité `depot_historiques`.
     *
     * @param  array<string, mixed>  $data  Payload validé {@see BulkChangeDepotStockRequest}
     */

    /**
     * Applique le même statut stock à plusieurs stocks.
     *
     * @param  array<string, mixed>  $data  Payload validé {@see BulkChangeStockStatusRequest}
     */
    public function bulkChangeStockStatus(array $data, ?int $userId): int
    {
        $stockStatusId = (int) ($data['stock_status_id'] ?? 0);

        if (! empty($data['select_all'])) {
            $filters = is_array($data['filters'] ?? null) ? $data['filters'] : [];
            $f = QueryFilterNormalizer::stock($filters);
            $queryBuilder = Stock::query();
            $this->applyStockListFilters($queryBuilder, $f);
            if (! empty($data['excluded_ids']) && is_array($data['excluded_ids'])) {
                $queryBuilder->whereNotIn('id', array_map('intval', $data['excluded_ids']));
            }

            return $queryBuilder->update([
                'stock_status_id' => $stockStatusId,
                'updated_by' => $userId,
            ]);
        }

        $ids = array_map('intval', $data['ids'] ?? []);
        if ($ids === []) {
            return 0;
        }

        return Stock::query()->whereIn('id', $ids)->update([
            'stock_status_id' => $stockStatusId,
            'updated_by' => $userId,
        ]);
    }

    public function bulkChangeDepot(array $data, ?int $userId): int
    {
        $depotId = (int) ($data['depot_id'] ?? 0);
        $isShowroomDepot = $this->isShowroomDepot($depotId);
        $isEntreeStockDepot = $this->isEntreeStockDepot($depotId);

        if (! empty($data['select_all'])) {
            $filters = is_array($data['filters'] ?? null) ? $data['filters'] : [];
            $f = QueryFilterNormalizer::stock($filters);
            $queryBuilder = Stock::query();
            $this->applyStockListFilters($queryBuilder, $f);
            if (! empty($data['excluded_ids']) && is_array($data['excluded_ids'])) {
                $queryBuilder->whereNotIn('id', array_map('intval', $data['excluded_ids']));
            }

            $stocks = (clone $queryBuilder)->get(['id', 'depot_id']);
            if ($isShowroomDepot) {
                $count = $this->bulkUpdateDepotWithShowroomExposure($queryBuilder, $depotId, $userId);
            } elseif ($isEntreeStockDepot) {
                $count = $this->bulkUpdateDepotWithEntreeStock($queryBuilder, $depotId, $userId);
            } else {
                $count = (int) $queryBuilder->update([
                    'depot_id' => $depotId,
                    'updated_by' => $userId,
                ]);
            }
            foreach ($stocks as $stock) {
                $this->recordStockDepotHistorique(
                    (int) $stock->id,
                    $stock->depot_id !== null ? (int) $stock->depot_id : null,
                    $depotId,
                    $userId,
                );
            }

            return $count;
        }

        $ids = array_map('intval', $data['ids'] ?? []);
        if ($ids === []) {
            return 0;
        }

        $stocks = Stock::query()->whereIn('id', $ids)->get(['id', 'depot_id']);
        $idQuery = Stock::query()->whereIn('id', $ids);
        if ($isShowroomDepot) {
            $count = $this->bulkUpdateDepotWithShowroomExposure($idQuery, $depotId, $userId);
        } elseif ($isEntreeStockDepot) {
            $count = $this->bulkUpdateDepotWithEntreeStock($idQuery, $depotId, $userId);
        } else {
            $count = (int) $idQuery->update([
                'depot_id' => $depotId,
                'updated_by' => $userId,
            ]);
        }
        foreach ($stocks as $stock) {
            $this->recordStockDepotHistorique(
                (int) $stock->id,
                $stock->depot_id !== null ? (int) $stock->depot_id : null,
                $depotId,
                $userId,
            );
        }

        return $count;
    }

    /**
     * Integration endpoint logic (system-to-system "approx" stock listing).
     *
     * Ordering:
     * 1) Exact identity match (modele, finition, version, color_ex, color_int) with VIN and not reserved.
     * 2) Arrival placeholder lines (VIN is NULL/empty) that do NOT match (modele, version, color_ex, color_int), not reserved.
     * 3) Same (modele, finition) with VIN and not reserved; color matches on color_ex OR color_int.
     *
     * Note: partner request might not send `finition`, so we fallback `finition = version`.
     * Groupe 1 and 2: only stock whose `depot` has `type_depot_id` = entrepôt stockage (same as `ENTREE_STOCK_TYPE_DEPOT_ID`).
     *
     * @param  array<string, mixed>  $query
     * @return Collection<int, Stock>|array<string, mixed>
     */
    public function listStockAproximit(array $query): Collection|array
    {
        //trim and clean and lowercase all the query parameters
        $marque = (string) $query['marque'];
        $modele = (string) $query['modele'];
        $finition = (string) $query['version'];
        $colorEx = (string) $query['color_ex'];
        $colorInt = (string) $query['color_int'];
        $finition = (string) $query['version'];

        $group1 = Stock::query()
            ->whereHas('depot', function (Builder $q) {
                $q->where('type_depot_id', self::ENTREE_STOCK_TYPE_DEPOT_ID);
            })
            ->where('modele', 'like', '%' . $modele . '%')
            ->where('marque', 'like', '%' . $marque . '%')
            ->where('finition', 'like', '%' . $finition . '%')
            ->where('color_ex', 'like', '%' . $colorEx . '%')
            ->where('color_int', 'like', '%' . $colorInt . '%')
            ->whereNotNull('vin')
            ->where('vin', '!=', '')
            ->where('reserved', false)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (Stock $s) {
                $s->setAttribute('in_arrivage', false);
                $s->setAttribute('match_type', 'exact');
                return $s;
            });

        $group2 = Stock::query()
            // ->whereHas('depot', function (Builder $q) {
            //     $q->where('type_depot_id', self::ENTREE_STOCK_TYPE_DEPOT_ID);
            // })
            ->where(function ($q) {
                $q->whereNull('vin')->orWhere('vin', '');
            })
            ->where('modele', 'like', '%' . $modele . '%')
            ->where('marque', 'like', '%' . $marque . '%')
            ->where('finition', 'like', '%' . $finition . '%')
            ->where('color_ex', 'like', '%' . $colorEx . '%')
            ->where('color_int', 'like', '%' . $colorInt . '%')
            ->where('reserved', false)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (Stock $s) {
                $s->setAttribute('in_arrivage', true);
                $s->setAttribute('match_type', 'exact');
                return $s;
            });

        $group1Ids = $group1->pluck('id')->all();

        $group3 = Stock::query()
            ->whereHas('depot', function (Builder $q) {
                $q->where('type_depot_id', self::ENTREE_STOCK_TYPE_DEPOT_ID);
            })
            ->whereNotNull('vin')
            ->where('vin', '!=', '')
            ->where('reserved', false)
            ->where('modele', 'like', '%' . $modele . '%')
            ->where('finition', 'like', '%' . $finition . '%')
            ->where('marque', 'like', '%' . $marque . '%')
            ->where(function ($q) use ($colorEx, $colorInt) {
                $q->where('color_ex', 'like', '%' . $colorEx . '%')->orWhere('color_int', 'like', '%' . $colorInt . '%');
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->values()
            ->map(function (Stock $s) {
                $s->setAttribute('in_arrivage', false);
                $s->setAttribute('match_type', 'partial');
                return $s;
            });

        $all = $group1->concat($group2)->concat($group3)->unique('id')->values();

        $paginated = (bool) ($query['paginated'] ?? false);
        if (! $paginated) {
            return $all;
        }

        $perPage = (int) ($query['per_page'] ?? 15);
        $perPage = max(1, min(100, $perPage));
        $page = (int) ($query['page'] ?? 1);
        $page = max(1, $page);

        $total = $all->count();
        $items = $all->slice(($page - 1) * $perPage, $perPage)->values()->all();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return PaginationPayload::fromPaginator($paginator);
    }

    /**
     * Recherche le premier véhicule correspondant à l'identité (marque, modele, finition, color_ex, color_int).
     *
     * Priorité :
     * 1) Le véhicule le plus ancien avec VIN non vide et non réservé  → `has_vin = true`.
     * 2) Si aucun résultat, le véhicule le plus ancien sans VIN (NULL / vide) et non réservé → `has_vin = false`.
     *
     * @param  array<string, mixed>  $query
     */
    public function getOldVinInStock(array $query): ?Stock
    {
        $marque   = (string) $query['marque'];
        $modele   = (string) $query['modele'];
        $finition = (string) $query['version'];
        $colorEx  = (string) $query['color_ex'];
        $colorInt = (string) $query['color_int'];

        $baseQuery = fn() => Stock::query()
            ->where('marque',   'like', '%' . $marque . '%')
            ->where('modele',   'like', '%' . $modele . '%')
            ->where('finition', 'like', '%' . $finition . '%')
            ->where('color_ex', 'like', '%' . $colorEx . '%')
            ->where('color_int', 'like', '%' . $colorInt . '%')
            ->where('reserved', false)
            // ->where(fn($q) => $q->whereNull('expose')->orWhere('expose', '!=', 1))
            // ->whereDoesntHave('depot', fn($q) => $q->whereIn('type', ['showroom', 'quarantaine']))
            ->orderByRaw('entree_stock_date IS NULL ASC, entree_stock_date ASC');

        // Groupe 1 : VIN renseigné
        $stock = $baseQuery()
            ->whereNotNull('vin')
            ->where('vin', '!=', '')
            ->whereHas('depot', function (Builder $q) {
                $q->where('type_depot_id', self::ENTREE_STOCK_TYPE_DEPOT_ID);
            })
            ->first();

        if ($stock) {
            $stock->setAttribute('in_arrivage', false);
            return $stock;
        }

        // Groupe 2 : fallback sans VIN
        $stock = $baseQuery()
            ->where(function ($q) {
                $q->whereNull('vin')->orWhere('vin', '');
            })
            ->whereHas('depot', function (Builder $q) {
                $q->where('type_depot_id', self::ENTREE_STOCK_TYPE_DEPOT_ID);
            })
            ->first();

        if ($stock) {
            $stock->setAttribute('in_arrivage', true);
        }

        return $stock;
    }

    /**
     * @param  array<string, mixed>  $data  Validated store payload
     */
    public function create(array $data, ?int $userId): Stock
    {
        try {
            $attributes = $this->stockCreateAttributes($data);
            if (isset($attributes['depot_id']) && (int) $attributes['depot_id'] > 0) {
                $newDep = (int) $attributes['depot_id'];
                $attributes = array_merge(
                    $attributes,
                    $this->showroomExposurePayload($this->isShowroomDepot($newDep)),
                    $this->entreeStockDepotPayload($newDep, null),
                );
            }
            $attributes['created_by'] = $userId;
            $stock = Stock::query()->create($attributes);
            if (isset($attributes['depot_id']) && (int) $attributes['depot_id'] > 0) {
                $this->recordStockDepotHistorique((int) $stock->id, null, (int) $attributes['depot_id'], $userId);
            }
            $stock->load(['depot', 'lot']);
            return $stock;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
    public function findWithRelations(int $id): ?Stock
    {
        try {
            return Stock::query()->with(['depot', 'lot'])->find($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param  array<string, mixed>  $validated  Validated update payload (present keys only)
     */
    public function update(int $id, array $validated, ?int $userId): ?Stock
    {
        try {
            $stock = Stock::findOrFail($id);
            $previousDepotId = $stock->depot_id;
            $payload = $this->stockUpdateAttributes($validated);
            if (array_key_exists('depot_id', $payload)) {
                $newDepotId = $payload['depot_id'] !== null ? (int) $payload['depot_id'] : null;
                $payload = array_merge(
                    $payload,
                    $this->showroomExposurePayload(
                        $this->isShowroomDepot($newDepotId),
                        $this->asNullableExposeDateString($stock->expose_date),
                    ),
                    $this->entreeStockDepotPayload(
                        $newDepotId,
                        $this->asNullableExposeDateString($stock->entree_stock_date),
                    ),
                );
            }
            if ($payload !== [] && $userId !== null) {
                $payload['updated_by'] = $userId;
            }
            if ($payload !== []) {
                $stock->update($payload);
                $stock->refresh();
            }
            // Business rule: toggling "combinaison rare" applies to the full configuration set
            // (marque, modele, finition, color_ex, color_int).
            if (array_key_exists('combinaison_rare', $payload)) {
                $rareValue = (bool) $payload['combinaison_rare'];
                $sameConfig = Stock::query();
                $this->applySameConfigurationFilter($sameConfig, $stock);
                $sameConfig->update(array_filter([
                    'combinaison_rare' => $rareValue,
                    'updated_by' => $userId,
                ], static fn($v) => $v !== null));
            }

            if (array_key_exists('depot_id', $payload)) {
                $newDepotId = $payload['depot_id'] !== null ? (int) $payload['depot_id'] : null;
                $this->recordStockDepotHistorique((int) $stock->id, $previousDepotId !== null ? (int) $previousDepotId : null, $newDepotId, $userId);
            }
            return Stock::query()->find($id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function applySameConfigurationFilter(Builder $query, Stock $stock): void
    {
        foreach (['modele', 'marque', 'color_ex', 'color_int', 'finition'] as $field) {
            $value = $stock->{$field};
            if ($value === null || $value === '') {
                $query->where(static function (Builder $q) use ($field): void {
                    $q->whereNull($field)->orWhere($field, '');
                });
                continue;
            }
            $query->where($field, $value);
        }
    }

    /**
     * @return array<string, true> Set of normalized rare configuration keys.
     */
    private function loadRareConfigurationKeys(): array
    {
        $keys = [];
        $rows = Stock::query()
            ->where('combinaison_rare', true)
            ->select(['modele', 'marque', 'finition', 'color_ex', 'color_int'])
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            $keys[$this->configurationKey(
                $row->modele,
                $row->marque,
                $row->color_ex,
                $row->color_int,
                $row->finition,
            )] = true;
        }

        return $keys;
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private function configurationKeyFromAttributes(array $attrs): string
    {
        return $this->configurationKey(
            isset($attrs['modele']) ? (string) $attrs['modele'] : null,
            isset($attrs['marque']) ? (string) $attrs['marque'] : null,
            isset($attrs['color_ex']) ? (string) $attrs['color_ex'] : null,
            isset($attrs['color_int']) ? (string) $attrs['color_int'] : null,
            isset($attrs['finition']) ? (string) $attrs['finition'] : null,
        );
    }

    private function configurationKey(
        ?string $modele,
        ?string $marque,
        ?string $colorEx,
        ?string $colorInt,
        ?string $finition,
    ): string {
        return implode('|', [
            $this->normalizeConfigurationValue($modele),
            $this->normalizeConfigurationValue($marque),
            $this->normalizeConfigurationValue($colorEx),
            $this->normalizeConfigurationValue($colorInt),
            $this->normalizeConfigurationValue($finition),
        ]);
    }

    private function normalizeConfigurationValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return Str::lower(trim($value));
    }

    public function delete(int $id): bool
    {
        $stock = Stock::query()->find($id);

        if (! $stock) {
            return false;
        }

        return (bool) $stock->delete();
    }

    /**
     * @param  array<string, mixed>  $data  Validated payload with depot_id
     */
    public function changeDepot(int $id, array $data, ?int $userId): ?Stock
    {
        $stock = Stock::query()->find($id);

        if (! $stock) {
            return null;
        }

        $previousDepotId = $stock->depot_id;
        $newDepotId = (int) $data['depot_id'];

        $stock->update(array_merge([
            'depot_id' => $newDepotId,
            'updated_by' => $userId,
        ], $this->showroomExposurePayload(
            $this->isShowroomDepot($newDepotId),
            $this->asNullableExposeDateString($stock->expose_date),
        ), $this->entreeStockDepotPayload(
            $newDepotId,
            $this->asNullableExposeDateString($stock->entree_stock_date),
        )));
        $this->recordStockDepotHistorique(
            (int) $stock->id,
            $previousDepotId !== null ? (int) $previousDepotId : null,
            $newDepotId,
            $userId,
        );
        $stock->load(['depot', 'lot']);

        return $stock;
    }

    /**
     * Import stock rows from a validated JSON batch. Each row runs in its own
     * transaction so one failure does not roll back siblings in the batch.
     *
     * - `stock_feed` (Alimenter stock) : chaque ligne valide crée un **nouveau** stock ;
     *   aucune recherche / fusion avec une ligne existante.
     * - `vin_update` (Mise à jour VIN) : pour chaque ligne, refus si le N° châssis existe déjà sur un autre
     *   véhicule ; sinon recherche d’un stock sans N° châssis avec la même commande + marque + modèle +
     *   finition + couleurs ; mise à jour du VIN et optionnellement du n° de lot si fournis.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{total: int, created: int, updated: int, skipped: int, messages: array<int, string>, created_details: array<int, string>, updated_details: array<int, string>}
     */
    public function importRows(array $rows, ?int $userId, string $importMode = 'stock_feed'): array
    {
        $total = count($rows);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $messages = [];
        $createdDetails = [];
        $updatedDetails = [];
        $rareConfigurationKeys = $importMode === 'stock_feed' ? $this->loadRareConfigurationKeys() : [];

        foreach ($rows as $index => $row) {
            $lineNo = $index + 1;
            $vinRaw = isset($row['vin']) ? trim((string) $row['vin']) : '';
            $lineLabel = 'Ligne ' . $lineNo . ($vinRaw !== '' ? ' (VIN: ' . $vinRaw . ')' : '');

            if ($importMode === 'stock_feed') {
                $missing = [];
                foreach (
                    [
                        'numero_commande' => 'N° cde',
                        'marque' => 'Marque',
                        'modele' => 'Modèle',
                        'finition' => 'Finition',
                        'color_ex' => 'Couleur Extérieure',
                        'color_int' => 'Couleur Intérieure',
                    ] as $field => $label
                ) {
                    $val = $row[$field] ?? null;
                    if ($val === null || (is_string($val) && trim($val) === '')) {
                        $missing[] = $label;
                    }
                }

                if ($missing !== []) {
                    $skipped++;
                    $messages[] = $lineLabel . ' — champ(s) obligatoire(s) manquant(s): ' . implode(', ', $missing) . '.';
                    continue;
                }

                try {
                    DB::transaction(function () use ($row, $userId, &$created, &$rareConfigurationKeys) {
                        $attrs = $this->stockAttributesFromImportRow($row);
                        $configKey = $this->configurationKeyFromAttributes($attrs);
                        if (isset($rareConfigurationKeys[$configKey])) {
                            $attrs['combinaison_rare'] = true;
                        }
                        $stock = Stock::query()->create(array_merge($attrs, [
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]));
                        if (! empty($stock->combinaison_rare)) {
                            $rareConfigurationKeys[$configKey] = true;
                        }
                        $created++;
                    });

                    $createdDetails[] = $lineLabel . ' — Nouvelle ligne créée (sans N° châssis dans le fichier).';
                } catch (\Throwable $e) {
                    $skipped++;
                    $messages[] = $lineLabel . ' — ' . $e->getMessage();
                }

                continue;
            }

            // import_mode === vin_update : mise à jour du VIN (+ lot optionnel) sur véhicule sans châssis
            $missingVinUpdate = [];
            foreach (
                [
                    'vin' => 'N° châssis',
                    'numero_commande' => 'N° cde',
                    'marque' => 'Marque',
                    'modele' => 'Modèle',
                    'finition' => 'Finition',
                    'color_ex' => 'Couleur Extérieure',
                    'color_int' => 'Couleur Intérieure',
                ] as $field => $label
            ) {
                $val = $row[$field] ?? null;
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    $missingVinUpdate[] = $label;
                }
            }

            if ($missingVinUpdate !== []) {
                $skipped++;
                $messages[] = $lineLabel . ' — champ(s) obligatoire(s) manquant(s): ' . implode(', ', $missingVinUpdate) . '.';

                continue;
            }

            if ($this->vinIsAlreadyAssignedToAStock($vinRaw)) {
                $skipped++;
                $messages[] = $lineLabel . ' — Ce N° châssis est déjà attribué à un autre véhicule.';

                continue;
            }

            $target = $this->findStockForVinUpdateMatch($row);

            if ($target === null) {
                $skipped++;
                $messages[] = $lineLabel . ' — Aucun véhicule sans N° châssis ne correspond (commande, marque, modèle, finition, couleurs).';

                continue;
            }

            try {
                DB::transaction(function () use ($row, $userId, $target, $vinRaw, &$updated) {
                    $attrs = [
                        'vin' => $vinRaw,
                        'updated_by' => $userId,
                    ];
                    $lotRaw = isset($row['numero_lot']) ? trim((string) $row['numero_lot']) : '';
                    $currentLot = $target->numero_lot !== null ? trim((string) $target->numero_lot) : '';
                    if ($lotRaw !== '' && $currentLot === '') {
                        $attrs['numero_lot'] = $lotRaw;
                    }
                    $target->update($attrs);
                    $updated++;
                });
                $updatedDetails[] = $lineLabel . ' — Stock #' . $target->getKey() . ' : N° châssis mis à jour.';
            } catch (\Throwable $e) {
                $skipped++;
                $messages[] = $lineLabel . ' — ' . $e->getMessage();
            }
        }

        return [
            'total' => $total,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'messages' => $messages,
            'created_details' => $createdDetails,
            'updated_details' => $updatedDetails,
        ];
    }

    /**     * Map validated store payload to Stock DB columns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function stockCreateAttributes(array $data): array
    {
        return array_filter([
            'modele'        => $data['modele'] ?? null,
            'finition'      => $data['version'] ?? null,
            'vin'           => $data['vin'] ?? null,
            'color_ex'      => $data['color_ex'] ?? null,
            'color_ex_code' => $data['color_ex_code'] ?? null,
            'color_int'     => $data['color_int'] ?? null,
            'color_int_code' => $data['color_int_code'] ?? null,
            'reserved'      => $data['reserved'] ?? false,
            'depot_id'      => isset($data['depot_id']) ? (int) $data['depot_id'] : null,
            'lot_id'        => isset($data['lot_id']) ? (int) $data['lot_id'] : null,
        ], fn($v) => $v !== null);
    }

    /**
     * Map validated update payload to Stock DB columns (only present keys).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function stockUpdateAttributes(array $data): array
    {
        $map = [
            'marque'                   => 'marque',
            'numero_commande'          => 'numero_commande',
            'modele'                   => 'modele',
            'version'                  => 'finition',
            'finition'                 => 'finition',
            'vin'                      => 'vin',
            'color_ex'                 => 'color_ex',
            'color_ex_code'            => 'color_ex_code',
            'color_int'                => 'color_int',
            'color_int_code'           => 'color_int_code',
            'client'                   => 'client',
            'type_client'              => 'type_client',
            'PGEO'                     => 'PGEO',
            'options'                  => 'options',
            'vendeur'                  => 'vendeur',
            'site_affecte'             => 'site_affecte',
            'date_creation_commande'   => 'date_creation_commande',
            'date_arrivage_prevu'      => 'date_arrivage_prevu',
            'date_arrivage_reelle'     => 'date_arrivage_reelle',
            'date_affectation'         => 'date_affectation',
            'depot_id'                 => 'depot_id',
            'stock_status_id'          => 'stock_status_id',
            'statut'                   => 'statut',
            'numero_lot'               => 'numero_lot',
            'numero_arrivage'          => 'numero_arrivage',
            'lot_id'                   => 'lot_id',
            'combinaison_rare'         => 'combinaison_rare',
        ];

        $out = [];
        foreach ($map as $inputKey => $column) {
            if (array_key_exists($inputKey, $data)) {
                $out[$column] = $data[$inputKey];
            }
        }

        return $out;
    }

    /**     * Ligne `stocks` avec vin NULL dont (modele, finition, color_ex, color_int)
     * correspondent à l’import (chaînes vides / null traitées comme « vides »).
     */
    private function vinIsAlreadyAssignedToAStock(string $vin): bool
    {
        $vin = trim($vin);
        if ($vin === '') {
            return false;
        }

        return Stock::query()
            ->where('vin', $vin)
            ->whereNotNull('vin')
            ->where('vin', '!=', '')
            ->exists();
    }

    /**
     * Véhicule sans N° châssis dont commande + identité correspondent à la ligne fichier.
     *
     * @param  array<string, mixed>  $row
     */
    public function findStockForVinUpdateMatch(array $row): ?Stock
    {
        $query = Stock::query()->where(function ($q) {
            $q->whereNull('vin')->orWhere('vin', '');
        });

        foreach (['numero_commande', 'marque', 'modele', 'finition', 'color_ex', 'color_int'] as $field) {
            $raw = $row[$field] ?? null;
            $val = is_string($raw) ? trim($raw) : trim((string) $raw);
            $query->where($field, $val);
        }

        return $query->first();
    }

    /**
     * Prévisualisation des mises à jour VIN (sans écrire en base).
     *
     * @param  array<int, array<string, mixed>>  $lines  Chaque entrée contient `line_no` + champs import.
     * @return array{matched: array<int, mixed>, unmatched: array<int, mixed>}
     */
    public function previewVinUpdate(array $lines): array
    {
        $matched = [];
        $unmatched = [];

        foreach ($lines as $item) {
            $lineNo = (int) ($item['line_no'] ?? 0);
            $row = $item;
            unset($row['line_no']);

            $vin = isset($row['vin']) ? trim((string) $row['vin']) : '';

            if ($vin !== '' && $this->vinIsAlreadyAssignedToAStock($vin)) {
                $unmatched[] = [
                    'line_no' => $lineNo,
                    'reason' => 'Ce N° châssis est déjà attribué à un autre véhicule.',
                    'row' => $row,
                ];

                continue;
            }

            $stock = $this->findStockForVinUpdateMatch($row);

            if ($stock === null) {
                $unmatched[] = [
                    'line_no' => $lineNo,
                    'reason' => 'Aucun véhicule sans N° châssis ne correspond (même commande, marque, modèle, finition, couleurs).',
                    'row' => $row,
                ];

                continue;
            }
            $lotRaw = isset($row['numero_lot']) ? trim((string) $row['numero_lot']) : '';
            $currentLot = $stock->numero_lot !== null ? trim((string) $stock->numero_lot) : '';
            $newNumeroLot = ($lotRaw !== '' && $currentLot === '') ? $lotRaw : null;

            $matched[] = [
                'line_no' => $lineNo,
                'stock_id' => $stock->getKey(),
                'new_vin' => $vin,
                'new_numero_lot' => $newNumeroLot,
                'row' => $row,
                'stock' => [
                    'id' => $stock->id,
                    'vin' => $stock->vin,
                    'numero_commande' => $stock->numero_commande,
                    'marque' => $stock->marque,
                    'modele' => $stock->modele,
                    'finition' => $stock->finition,
                    'color_ex' => $stock->color_ex,
                    'color_int' => $stock->color_int,
                    'numero_lot' => $stock->numero_lot,
                ],
            ];
        }

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
        ];
    }


    /**
     * Map import keys to DB columns; omit empties. Dates normalized to Y-m-d.
     * `date_desaffectation` is ignored until a DB column exists.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function stockAttributesFromImportRow(array $row): array
    {
        $mapping = [
            'vin' => 'vin',
            'numero_commande' => 'numero_commande',
            'statut' => 'statut',
            'date_arrivage_prevu' => 'date_arrivage_prevu',
            'client' => 'client',
            'type_client' => 'type_client',
            'PGEO' => 'PGEO',
            'marque' => 'marque',
            'modele' => 'modele',
            'finition' => 'finition',
            'options' => 'options',
            'color_ex' => 'color_ex',
            'color_int' => 'color_int',
            'vendeur' => 'vendeur',
            'site_affecte' => 'site_affecte',
            'date_creation_commande' => 'date_creation_commande',
            'date_affectation' => 'date_affectation',
            'date_arrivage_reelle' => 'date_arrivage_reelle',
            'version' => 'version',
            'numero_lot' => 'numero_lot',
        ];

        $dateColumns = [
            'date_arrivage_prevu',
            'date_arrivage_reelle',
            'date_affectation',
            'date_creation_commande',
        ];

        $out = [];

        foreach ($mapping as $importKey => $column) {
            if (! array_key_exists($importKey, $row)) {
                continue;
            }
            $val = $row[$importKey];
            if ($val === null || $val === '') {
                continue;
            }

            if (in_array($column, $dateColumns, true)) {
                $normalized = $this->normalizeImportDateValue($val);
                if ($normalized !== null) {
                    $out[$column] = $normalized;
                }

                continue;
            }

            $out[$column] = is_string($val) ? $val : (string) $val;
        }

        $out['stock_status_id'] = isset($row['stock_status_id']) && $row['stock_status_id'] !== null && $row['stock_status_id'] !== ''
            ? (int) $row['stock_status_id']
            : self::DEFAULT_STOCK_STATUS_ID;

        return $out;
    }

    private function normalizeImportDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::parse($value)->format('Y-m-d');
        }

        $str = is_string($value) ? trim($value) : (string) $value;

        if (preg_match('/^(\d{4}-\d{2}-\d{2})(?:[Tt\s]|$)/', $str, $m)) {
            return $m[1];
        }

        // Dates type import français `jj/mm/aaaa` (Carbon peut les lire en `m/j` selon la locale).
        if (preg_match('/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})$/', $str, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{4})[\/.\-](\d{1,2})[\/.\-](\d{1,2})$/', $str, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Fil chronologique Usine → dépôts pour la traçabilité (UI modal).
     *
     * @return array{stock: array<string, mixed>, timeline: array<int, array<string, mixed>>}
     */
    public function depotHistoriqueTimeline(Stock $stock): array
    {
        $historiques = DepotHistorique::query()
            ->where('stock_id', $stock->getKey())
            ->with(['depot', 'creator'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $timeline = [];

        $timeline[] = [
            'kind' => 'usine',
            'title' => 'Usine',
            'subtitle' => 'Fabrication — avant affectation à un dépôt logistique',
            'date' => $stock->created_at !== null ? $stock->created_at->toDateString() : null,
            'at' => $stock->created_at !== null ? $stock->created_at->toIso8601String() : null,
        ];

        foreach ($historiques as $h) {
            $creator = $h->creator;
            $timeline[] = [
                'kind' => 'depot',
                'id' => $h->id,
                'date' => $h->created_at !== null ? $h->created_at->format('Y-m-d') : null,
                'at' => $h->created_at !== null ? $h->created_at->toIso8601String() : null,
                'depot' => $h->depot !== null ? [
                    'id' => $h->depot->id,
                    'name' => $h->depot->name,
                    'type' => $h->depot->type,
                ] : null,
                'created_by_user' => $creator !== null ? [
                    'id' => $creator->id,
                    'nom' => $creator->nom,
                    'prenom' => $creator->prenom,
                ] : null,
            ];
        }

        return [
            'stock' => [
                'id' => $stock->id,
                'vin' => $stock->vin,
                'numero_commande' => $stock->numero_commande,
                'depot_id' => $stock->depot_id,
                'created_at' => $stock->created_at !== null ? $stock->created_at->toIso8601String() : null,
            ],
            'timeline' => $timeline,
        ];
    }
}
