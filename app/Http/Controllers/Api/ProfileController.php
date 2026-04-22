<?php

namespace App\Http\Controllers\Api;

use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\BulkUpdateProfileStatusRequest;
use App\Http\Requests\Profile\IndexProfileRequest;
use App\Http\Requests\Profile\StoreProfileRequest;
use App\Http\Requests\Profile\UpdateProfilePermissionsRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Profile;
use App\Services\Profile\ProfileService;
use App\Traits\ApiResponsable;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    use ApiResponsable;

    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function index(IndexProfileRequest $request): JsonResponse
    {
        try {
            $profiles = $this->profileService->list($request->validated());

            return $this->success($profiles, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->profileService->create($request->toDto());
            $this->audit('create', 'profiles', (int) $profile->id, null, $request->validated());

            return $this->success($profile, MessageKey::CREATED, 201);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function show(Profile $profile): JsonResponse
    {
        return $this->success($profile, MessageKey::FETCHED);
    }

    public function update(UpdateProfileRequest $request, Profile $profile): JsonResponse
    {
        try {
            $updated = $this->profileService->update($profile->id, $request->validated());

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

            $this->audit('update', 'profiles', $profile->id, null, $request->validated());

            return $this->success($updated, MessageKey::UPDATED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage(), 500);
        }
    }

    public function destroy(Profile $profile): JsonResponse
    {
        if (! $this->profileService->delete($profile->id)) {
            return $this->error(MessageKey::NOT_FOUND, null, 404);
        }

        $this->audit('delete', 'profiles', $profile->id);

        return $this->success(null, MessageKey::DELETED);
    }

    public function bulkUpdateStatus(BulkUpdateProfileStatusRequest $request): JsonResponse
    {
        try {
            $updated = $this->profileService->bulkUpdateStatut($request->validated());
            $this->audit('bulk_update_status', 'profiles', null, null, $request->validated());

            return $this->success(['updated' => $updated], MessageKey::UPDATED);
        } catch (\InvalidArgumentException $e) {
            return $this->error(MessageKey::INVALID, $e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function permissions(Profile $profile): JsonResponse
    {
        try {
            $matrix = $this->profileService->permissionMatrix((int) $profile->id);

            return $this->success($matrix, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function updatePermissions(UpdateProfilePermissionsRequest $request, Profile $profile): JsonResponse
    {
        try {
            $validated = $request->validated();
            $this->profileService->syncPermissions((int) $profile->id, $validated['permissions']);
            $this->audit('sync_permissions', 'profiles', (int) $profile->id, null, ['permissions' => $validated['permissions']]);

            return $this->success(
                $this->profileService->permissionMatrix((int) $profile->id),
                MessageKey::UPDATED
            );
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }
}
