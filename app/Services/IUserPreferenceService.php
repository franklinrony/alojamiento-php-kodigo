<?php

namespace App\Services;

/**
 * Interface IUserPreferenceService
 * Interfaz para el servicio de preferencias de usuario
 */
interface IUserPreferenceService
{
    /**
     * Obtiene las preferencias de un usuario
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserPreferences(int $userId): ?array;

    /**
     * Actualiza las preferencias de un usuario
     *
     * @param int $userId
     * @param array $preferences
     * @return bool
     */
    public function updatePreferences(int $userId, array $preferences): bool;

    /**
     * Obtiene una preferencia específica de un usuario
     *
     * @param int $userId
     * @param string $key
     * @return mixed|null
     */
    public function getPreference(int $userId, string $key);

    /**
     * Establece una preferencia específica de un usuario
     *
     * @param int $userId
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function setPreference(int $userId, string $key, $value): bool;

    /**
     * Crea preferencias por defecto para un usuario
     *
     * @param int $userId
     * @return bool
     */
    public function createDefaultPreferences(int $userId): bool;
}
