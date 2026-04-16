<?php

namespace App\Services\Stock;

use App\Models\Stock;
use App\Support\PaginationPayload;
use App\Support\QueryFilterNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockService
{
    /**
     * @param  array<string, mixed>  $query  Validated index / query parameters
     * @return array<string, mixed>|Collection<int, Stock>
     */
    public function list(array $query): array|Collection
    {
        $f = QueryFilterNormalizer::stock($query);
        $queryBuilder = Stock::query()->with(['depot', 'stockStatus']);

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
        $marque= (string) $query['marque'];
        $modele = (string) $query['modele'];
        $finition = (string) $query['version'];
        $colorEx = (string) $query['color_ex'];
        $colorInt = (string) $query['color_int'];
        $finition = (string) $query['version'];

        $group1 = Stock::query()
            ->where('modele','like', '%'.$modele.'%')
            ->where('marque','like', '%'.$marque.'%')
            ->where('finition','like', '%'.$finition.'%')
            ->where('color_ex','like', '%'.$colorEx.'%')
            ->where('color_int','like', '%'.$colorInt.'%')
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
            ->where('modele','like', '%'.$modele.'%')
            ->where('marque','like', '%'.$marque.'%')
            ->where('finition','like', '%'.$finition.'%')
            ->where('color_ex','like', '%'.$colorEx.'%')
            ->where('color_int','like', '%'.$colorInt.'%')
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
            ->where('modele','like', '%'.$modele.'%')
            ->where('finition','like', '%'.$finition.'%')
            ->where('marque','like', '%'.$marque.'%')
            ->where(function ($q) use ($colorEx, $colorInt) {
                $q->where('color_ex','like', '%'.$colorEx.'%')->orWhere('color_int','like', '%'.$colorInt.'%');
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->reject(fn (Stock $s) => in_array($s->id, $group1Ids, true))
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
     * Rules:
     * - VIN vide / absent : création d’une nouvelle ligne avec les données reçues.
     * - VIN renseigné : recherche d’une ligne existante avec `vin` NULL et les mêmes
     *   (modele, finition, color_ex, color_int) ; si trouvée, mise à jour (dont le VIN) ;
     *   sinon la ligne est ignorée et un message est ajouté.
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
            $lineLabel = 'Ligne '.$lineNo.($vinRaw !== '' ? ' (VIN: '.$vinRaw.')' : '');

            if ($importMode === 'stock_feed') {
                $missing = [];
                foreach ([
                    'numero_commande' => 'N° cde',
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
                    $messages[] = $lineLabel.' — champ(s) obligatoire(s) manquant(s): '.implode(', ', $missing).'.';
                    continue;
                }
            }

            if ($vinRaw === '') {
                try {
                    DB::transaction(function () use ($row, $userId, &$created) {
                        $attrs = $this->stockAttributesFromImportRow($row);
                        Stock::query()->create(array_merge($attrs, [
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]));
                        $created++;
                    });
                    $createdDetails[] = $lineLabel.' — Nouvelle ligne créée (sans N° châssis dans le fichier).';
                } catch (\Throwable $e) {
                    $skipped++;
                    $messages[] = $lineLabel.' — '.$e->getMessage();
                }

                continue;
            }

            $placeholder = $this->findStockWithoutVinMatchingIdentity($row);

            if ($placeholder === null) {
                $skipped++;
                $messages[] = $lineLabel.' — Aucune ligne sans VIN ne correspond (modèle, finition, couleurs).';

                continue;
            }

            try {
                DB::transaction(function () use ($row, $userId, $placeholder, &$updated) {
                    $attrs = $this->stockAttributesFromImportRow($row);
                    $placeholder->update(array_merge($attrs, [
                        'updated_by' => $userId,
                    ]));
                    $updated++;
                });
                $updatedDetails[] = $lineLabel.' — Correspondance sans VIN #'.$placeholder->getKey()
                    .' : mise à jour avec ce N° châssis.';
            } catch (\Throwable $e) {
                $skipped++;
                $messages[] = $lineLabel.' — '.$e->getMessage();
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

    /**
     * Ligne `stocks` avec vin NULL dont (modele, finition, color_ex, color_int)
     * correspondent à l’import (chaînes vides / null traitées comme « vides »).
     */
    private function findStockWithoutVinMatchingIdentity(array $row): ?Stock
    {
        $query = Stock::query()->whereNull('vin');

        foreach (['modele', 'finition', 'color_ex', 'color_int'] as $field) {
            $raw = $row[$field] ?? null;
            if ($raw === null || (is_string($raw) && trim($raw) === '')) {
                $query->where(function ($q) use ($field) {
                    $q->whereNull($field)->orWhere($field, '');
                });
            } else {
                $query->where($field, is_string($raw) ? trim($raw) : (string) $raw);
            }
        }

        return $query->first();
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
