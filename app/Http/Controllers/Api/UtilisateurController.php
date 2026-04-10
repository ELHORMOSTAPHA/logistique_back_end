<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Utilisateur\IndexUtilisateurRequest;
use App\Http\Requests\Utilisateur\StoreUtilisateurRequest;
use App\Http\Requests\Utilisateur\UpdateUtilisateurRequest;
use App\Models\User;
use App\Services\Utilisateur\UtilisateurService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;

class UtilisateurController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly UtilisateurService $utilisateurService,
    ) {}

    public function index(IndexUtilisateurRequest $request): JsonResponse
    {
        try {
            $data = $this->utilisateurService->list($request->validated());

            return $this->success($data, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreUtilisateurRequest $request): JsonResponse
    {
        try {
            $user = $this->utilisateurService->create($request->toDto());

            return $this->success($user->load('profile'), MessageKey::CREATED, 201);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function show(User $utilisateur): JsonResponse
    {
        $user = $this->utilisateurService->find($utilisateur->id);

        return $this->success($user, MessageKey::FETCHED);
    }

    public function update(UpdateUtilisateurRequest $request, User $utilisateur): JsonResponse
    {
        try {
            $updated = $this->utilisateurService->update($utilisateur->id, $request->validated());

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function destroy(User $utilisateur): JsonResponse
    {
        if (! $this->utilisateurService->delete($utilisateur->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        return $this->success(null, MessageKey::DELETED);
    }
}
