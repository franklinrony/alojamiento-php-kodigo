<?php

namespace App\Services;

use App\Models\Permission;

/**
 * Interface IPermissionService
 * Interfaz para el servicio de permisos
 */
interface IPermissionService
{
    /**
     * Crea un nuevo permiso
     *
     * @param array $permissionData
     * @return Permission
     */
    public function createPermission(array $permissionData): Permission;

    /**
     * Actualiza un permiso existente
     *
     * @param int $permissionId
     * @param array $permissionData
     * @return Permission|null
     */
    public function updatePermission(int $permissionId, array $permissionData): ?Permission;

    /**
     * Verifica si un permiso existe por su nombre
     *
     * @param string $name
     * @return bool
     */
    public function permissionExists(string $name): bool;

    /**
     * Obtiene los roles que tienen un permiso específico
     *
     * @param int $permissionId
     * @return array
     */
    public function getRolesWithPermission(int $permissionId): array;

    /**
     * Obtiene todos los permisos disponibles
     *
     * @return Permission[]
     */
    public function getAllPermissions(): array;
}
