<?php

namespace App\Support;

/**
 * Normalizes validated query strings for list endpoints (replaces List*Dto::fromArray).
 *
 * @phpstan-type StockFilters array{
 *   name: ?string, from: ?string, to: ?string, modele: ?string, vin: ?string,
 *   reserved: ?bool, depot_id: ?int, lot_id: ?int, per_page: int, page: ?int, sort_by: ?string,
 *   sort_order: ?string, paginated: ?bool
 * }
 */
final class QueryFilterNormalizer
{
    /**
     * @param  array<string, mixed>  $query
     * @return StockFilters
     */
    public static function stock(array $query): array
    {
        $reserved = null;
        if (array_key_exists('reserved', $query) && $query['reserved'] !== null && $query['reserved'] !== '') {
            $reserved = filter_var($query['reserved'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($reserved === null) {
                $reserved = (bool) $query['reserved'];
            }
        }

        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'name' => isset($query['name']) && $query['name'] !== '' ? (string) $query['name'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'modele' => isset($query['modele']) && $query['modele'] !== '' ? (string) $query['modele'] : null,
            'vin' => isset($query['vin']) && $query['vin'] !== '' ? (string) $query['vin'] : null,
            'reserved' => $reserved,
            'depot_id' => isset($query['depot_id']) && $query['depot_id'] !== '' ? (int) $query['depot_id'] : null,
            'lot_id' => isset($query['lot_id']) && $query['lot_id'] !== '' ? (int) $query['lot_id'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function depot(array $query): array
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'name' => isset($query['name']) && $query['name'] !== '' ? (string) $query['name'] : null,
            'type' => isset($query['type']) && $query['type'] !== '' ? (string) $query['type'] : null,
            'created_at' => isset($query['created_at']) && $query['created_at'] !== '' ? (string) $query['created_at'] : null,
            'updated_at' => isset($query['updated_at']) && $query['updated_at'] !== '' ? (string) $query['updated_at'] : null,
            'deleted_at' => isset($query['deleted_at']) && $query['deleted_at'] !== '' ? (string) $query['deleted_at'] : null,
            'created_by' => isset($query['created_by']) && $query['created_by'] !== '' ? (int) $query['created_by'] : null,
            'deleted_by' => isset($query['deleted_by']) && $query['deleted_by'] !== '' ? (int) $query['deleted_by'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function lot(array $query): array
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'numero_lot' => isset($query['numero_lot']) && $query['numero_lot'] !== '' ? (string) $query['numero_lot'] : null,
            'numero_arrivage' => isset($query['numero_arrivage']) && $query['numero_arrivage'] !== '' ? (string) $query['numero_arrivage'] : null,
            'statut' => isset($query['statut']) && $query['statut'] !== '' ? (string) $query['statut'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function historique(array $query): array
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'user_id' => isset($query['user_id']) && $query['user_id'] !== '' ? (string) $query['user_id'] : null,
            'action' => isset($query['action']) && $query['action'] !== '' ? (string) $query['action'] : null,
            'table_name' => isset($query['table_name']) && $query['table_name'] !== '' ? (string) $query['table_name'] : null,
            'record_id' => isset($query['record_id']) && $query['record_id'] !== '' ? (int) $query['record_id'] : null,
            'keyword' => isset($query['keyword']) && $query['keyword'] !== '' ? (string) $query['keyword'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function demandeReservation(array $query): array
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'stock_id' => isset($query['stock_id']) && $query['stock_id'] !== '' ? (int) $query['stock_id'] : null,
            'statut' => isset($query['statut']) && $query['statut'] !== '' ? (string) $query['statut'] : null,
            'id_demande' => isset($query['id_demande']) && $query['id_demande'] !== '' ? (string) $query['id_demande'] : null,
            'nom_commercial' => isset($query['nom_commercial']) && $query['nom_commercial'] !== '' ? (string) $query['nom_commercial'] : null,
            'keyword' => isset($query['keyword']) && $query['keyword'] !== '' ? (string) $query['keyword'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function utilisateur(array $query): array
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'keyword' => isset($query['keyword']) && $query['keyword'] !== '' ? (string) $query['keyword'] : null,
            'nom' => isset($query['nom']) && $query['nom'] !== '' ? (string) $query['nom'] : null,
            'prenom' => isset($query['prenom']) && $query['prenom'] !== '' ? (string) $query['prenom'] : null,
            'email' => isset($query['email']) && $query['email'] !== '' ? (string) $query['email'] : null,
            'statut' => isset($query['statut']) && $query['statut'] !== '' ? (string) $query['statut'] : null,
            'id_profile' => isset($query['id_profile']) && $query['id_profile'] !== '' ? (int) $query['id_profile'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function profile(array $query): array
    {
        $per_page = isset($query['per_page']) ? min(100, max(1, (int) $query['per_page'])) : 15;

        $sort_order = null;
        if (isset($query['sort_order']) && $query['sort_order'] !== '') {
            $sort_order = strtolower((string) $query['sort_order']);
        }

        return [
            'nom' => isset($query['nom']) && $query['nom'] !== '' ? (string) $query['nom'] : null,
            'libelle' => isset($query['libelle']) && $query['libelle'] !== '' ? (string) $query['libelle'] : null,
            'statut' => isset($query['statut']) && $query['statut'] !== '' ? (string) $query['statut'] : null,
            'from' => isset($query['from']) && $query['from'] !== '' ? (string) $query['from'] : null,
            'to' => isset($query['to']) && $query['to'] !== '' ? (string) $query['to'] : null,
            'per_page' => $per_page,
            'page' => isset($query['page']) ? max(1, (int) $query['page']) : null,
            'sort_by' => isset($query['sort_by']) && $query['sort_by'] !== '' ? (string) $query['sort_by'] : null,
            'sort_order' => $sort_order,
            'paginated' => array_key_exists('paginated', $query) && $query['paginated'] !== null
                ? (bool) $query['paginated']
                : null,
        ];
    }
}
