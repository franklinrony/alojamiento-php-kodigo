<?php

namespace App\Repositories;

interface IUserActivityRepository extends IRepository
{
    /**
     * Get all activities for a user
     * @param int $userId
     * @return array
     */
    public function getActivitiesByUser(int $userId): array;

    /**
     * Log a new activity
     * @param int $userId
     * @param string $type
     * @param string|null $details
     * @return bool
     */
    public function logActivity(int $userId, string $type, ?string $details = null): bool;
}
