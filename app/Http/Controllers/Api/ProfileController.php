<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Profile\UpdateProfileDto;
use App\Enums\MessageKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\IndexProfileRequest;
use App\Http\Requests\Profile\StoreProfileRequest;
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
            $profiles = $this->profileService->list($request->toFilterDto());

            return $this->success($profiles, MessageKey::FETCHED);
        } catch (\Exception $e) {
            return $this->error(MessageKey::SERVER, $e->getMessage());
        }
    }

    public function store(StoreProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->profileService->create($request->toDto());

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
            $dto = UpdateProfileDto::fromRequest($request);
            $updated = $this->profileService->update($profile->id, $dto);

            if (! $updated) {
                return $this->error(MessageKey::NOT_FOUND, null, 404);
            }

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

        return $this->success(null, MessageKey::DELETED);
    }
}
