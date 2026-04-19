<?php

namespace App\Services\Stock;

use App\Models\Stock;
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

    /**
     * @param  array<string, mixed>  $query  Validated index / query parameters
     * @return array<string, mixed>|Collection<int, Stock>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::stock($query);
        $queryBuilder = Stock::query()->with(['depot', 'stockStatus']);
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
     *
     * @param  array<string, mixed>  $data  Payload validé {@see BulkChangeDepotStockRequest}
     */
    public function bulkChangeDepot(array $data, ?int $userId): int
    {
        $depotId = (int) ($data['depot_id'] ?? 0);

        if (! empty($data['select_all'])) {
            $filters = is_array($data['filters'] ?? null) ? $data['filters'] : [];
            $f = QueryFilterNormalizer::stock($filters);
            $queryBuilder = Stock::query();
            $this->applyStockListFilters($queryBuilder, $f);
            if (! empty($data['excluded_ids']) && is_array($data['excluded_ids'])) {
                $queryBuilder->whereNotIn('id', array_map('intval', $data['excluded_ids']));
            }

            return $queryBuilder->update([
                'depot_id' => $depotId,
                'updated_by' => $userId,
            ]);
        }

        $ids = array_map('intval', $data['ids'] ?? []);
        if ($ids === []) {
            return 0;
        }

        return Stock::query()->whereIn('id', $ids)->update([
            'depot_id' => $depotId,
            'updated_by' => $userId,
        ]);
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
                $s->setAttribute('match_type', 'arrival');
                return $s;
            });

        $group1Ids = $group1->pluck('id')->all();

        $group3 = Stock::query()
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
            ->reject(fn(Stock $s) => in_array($s->id, $group1Ids, true))
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
            ->orderBy('created_at', 'asc');

        // Groupe 1 : VIN renseigné
        $stock = $baseQuery()
            ->whereNotNull('vin')
            ->where('vin', '!=', '')
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
            $attributes['created_by'] = $userId;
            $stock = Stock::query()->create($attributes);
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
            $payload = $this->stockUpdateAttributes($validated);
            if ($payload !== []) {
                $stock->update($payload);
            }
            return $stock;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
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

        $stock->update([
            'depot_id' => (int) $data['depot_id'],
            'updated_by' => $userId,
        ]);
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

        foreach ($rows as $index => $row) {
            $lineNo = $index + 1;
            $vinRaw = isset($row['vin']) ? trim((string) $row['vin']) : '';
            $lineLabel = 'Ligne ' . $lineNo . ($vinRaw !== '' ? ' (VIN: ' . $vinRaw . ')' : '');

            if ($importMode === 'stock_feed') {
                $missing = [];
                foreach ([
                    'numero_commande' => 'N° cde',
                    'marque' => 'Marque',
                    'modele' => 'Modèle',
                    'finition' => 'Finition',
                    'color_ex' => 'Couleur Extérieure',
                    'color_int' => 'Couleur Intérieure',
                ] as $field => $label) {
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
                    DB::transaction(function () use ($row, $userId, &$created) {
                        $attrs = $this->stockAttributesFromImportRow($row);
                        Stock::query()->create(array_merge($attrs, [
                            'depot_id' => $this->resolveDepotIdForStockFeedRow($row),
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]));
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
            foreach ([
                'vin' => 'N° châssis',
                'numero_commande' => 'N° cde',
                'marque' => 'Marque',
                'modele' => 'Modèle',
                'finition' => 'Finition',
                'color_ex' => 'Couleur Extérieure',
                'color_int' => 'Couleur Intérieure',
            ] as $field => $label) {
                $val = $row[$field] ?? null;
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    $missingVinUpdate[] = $label;
                }
            }

            if ($missingVinUpdate !== []) {
                $skipped++;
                $messages[] = $lineLabel.' — champ(s) obligatoire(s) manquant(s): '.implode(', ', $missingVinUpdate).'.';

                continue;
            }

            if ($this->vinIsAlreadyAssignedToAStock($vinRaw)) {
                $skipped++;
                $messages[] = $lineLabel.' — Ce N° châssis est déjà attribué à un autre véhicule.';

                continue;
            }

            $target = $this->findStockForVinUpdateMatch($row);

            if ($target === null) {
                $skipped++;
                $messages[] = $lineLabel.' — Aucun véhicule sans N° châssis ne correspond (commande, marque, modèle, finition, couleurs).';

                continue;
            }

            try {
                DB::transaction(function () use ($row, $userId, $target, $vinRaw, &$updated) {
                    $attrs = [
                        'vin' => $vinRaw,
                        'updated_by' => $userId,
                    ];
                    $lotRaw = isset($row['numero_lot']) ? trim((string) $row['numero_lot']) : '';
                    if ($lotRaw !== '') {
                        $attrs['numero_lot'] = $lotRaw;
                    }
                    $target->update($attrs);
                    $updated++;
                });
                $updatedDetails[] = $lineLabel.' — Stock #'.$target->getKey().' : N° châssis mis à jour.';
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
            'modele'         => 'modele',
            'version'        => 'finition',
            'vin'            => 'vin',
            'color_ex'       => 'color_ex',
            'color_ex_code'  => 'color_ex_code',
            'color_int'      => 'color_int',
            'color_int_code' => 'color_int_code',
            'reserved'       => 'reserved',
            'depot_id'       => 'depot_id',
            'lot_id'         => 'lot_id',
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
            $newNumeroLot = $lotRaw !== '' ? $lotRaw : null;

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
     * Dépôt pour une ligne « Alimenter stock » : défaut {@see DEFAULT_STOCK_FEED_DEPOT_ID},
     * sauf si la ligne contient un `depot_id` entier strictement positif (futur fichier / API).
     *
     * @param  array<string, mixed>  $row
     */
    private function resolveDepotIdForStockFeedRow(array $row): int
    {
        $raw = $row['depot_id'] ?? null;
        if ($raw !== null && $raw !== '') {
            $d = (int) $raw;
            if ($d > 0) {
                return $d;
            }
        }

        return self::DEFAULT_STOCK_FEED_DEPOT_ID;
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

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
