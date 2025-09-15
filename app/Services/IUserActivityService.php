<?php

namespace App\Services;

use App\Models\UserActivity;

/**
 * Interface IUserActivityService
 * Interfaz para el servicio de actividades de usuario
 */
interface IUserActivityService
{
    /**
     * Registra una nueva actividad de usuario
     *
     * @param int $userId
     * @param string $type
     * @param string|null $details
     * @return bool
     */
    public function logActivity(int $userId, string $type, ?string $details = null): bool;

    /**
     * Obtiene todas las actividades de un usuario
     *
     * @param int $userId
     * @return array
     */
    public function getActivitiesByUser(int $userId): array;

    /**
     * Obtiene actividades recientes de un usuario
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentActivities(int $userId, int $limit = 10): array;

    /**
     * Obtiene estadísticas de actividades de un usuario
     *
     * @param int $userId
     * @return array
     */
    public function getActivityStats(int $userId): array;
}
