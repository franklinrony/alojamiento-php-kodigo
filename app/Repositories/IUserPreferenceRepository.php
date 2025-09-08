<?php

namespace App\Repositories;

interface IUserPreferenceRepository extends IRepository
{
    /**
     * Get user preferences
     * @param int $userId
     * @return array|null
     */
    public function getUserPreferences(int $userId): ?array;

    /**
     * Update user preferences
     * @param int $userId
     * @param array $preferences
     * @return bool
     */
    public function updatePreferences(int $userId, array $preferences): bool;
}
