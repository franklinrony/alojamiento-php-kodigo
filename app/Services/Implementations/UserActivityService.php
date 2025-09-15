<?php

namespace App\Services\Implementations;

use App\Services\IUserActivityService;
use App\Repositories\IUserActivityRepository;

/**
 * Class UserActivityService
 * Implementación del servicio de actividades de usuario
 */
class UserActivityService implements IUserActivityService
{
    /**
     * @var IUserActivityRepository
     */
    private $userActivityRepository;

    /**
     * @param IUserActivityRepository $userActivityRepository
     */
    public function __construct(IUserActivityRepository $userActivityRepository)
    {
        $this->userActivityRepository = $userActivityRepository;
    }

    /**
     * @inheritDoc
     */
    public function logActivity(int $userId, string $type, ?string $details = null): bool
    {
        return $this->userActivityRepository->logActivity($userId, $type, $details);
    }

    /**
     * @inheritDoc
     */
    public function getActivitiesByUser(int $userId): array
    {
        return $this->userActivityRepository->getActivitiesByUser($userId);
    }

    /**
     * @inheritDoc
     */
    public function getRecentActivities(int $userId, int $limit = 10): array
    {
        $allActivities = $this->getActivitiesByUser($userId);
        return array_slice($allActivities, 0, $limit);
    }

    /**
     * @inheritDoc
     */
    public function getActivityStats(int $userId): array
    {
        $activities = $this->getActivitiesByUser($userId);
        
        $stats = [
            'total_activities' => count($activities),
            'activities_by_type' => [],
            'recent_activity' => null
        ];

        // Contar actividades por tipo
        foreach ($activities as $activity) {
            $type = $activity->getType();
            if (!isset($stats['activities_by_type'][$type])) {
                $stats['activities_by_type'][$type] = 0;
            }
            $stats['activities_by_type'][$type]++;
        }

        // Actividad más reciente
        if (!empty($activities)) {
            $stats['recent_activity'] = $activities[0];
        }

        return $stats;
    }
}
