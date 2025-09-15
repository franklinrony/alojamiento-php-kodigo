<?php

namespace App\Services\Implementations;

use App\Services\IUserPreferenceService;
use App\Repositories\IUserPreferenceRepository;

/**
 * Class UserPreferenceService
 * Implementación del servicio de preferencias de usuario
 */
class UserPreferenceService implements IUserPreferenceService
{
    /**
     * @var IUserPreferenceRepository
     */
    private $userPreferenceRepository;

    /**
     * @param IUserPreferenceRepository $userPreferenceRepository
     */
    public function __construct(IUserPreferenceRepository $userPreferenceRepository)
    {
        $this->userPreferenceRepository = $userPreferenceRepository;
    }

    /**
     * @inheritDoc
     */
    public function getUserPreferences(int $userId): ?array
    {
        return $this->userPreferenceRepository->getUserPreferences($userId);
    }

    /**
     * @inheritDoc
     */
    public function updatePreferences(int $userId, array $preferences): bool
    {
        return $this->userPreferenceRepository->updatePreferences($userId, $preferences);
    }

    /**
     * @inheritDoc
     */
    public function getPreference(int $userId, string $key)
    {
        $preferences = $this->getUserPreferences($userId);
        return $preferences[$key] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setPreference(int $userId, string $key, $value): bool
    {
        return $this->updatePreferences($userId, [$key => $value]);
    }

    /**
     * @inheritDoc
     */
    public function createDefaultPreferences(int $userId): bool
    {
        $defaultPreferences = [
            'notifications' => 'all',
            'email_updates' => true
        ];

        return $this->updatePreferences($userId, $defaultPreferences);
    }
}
