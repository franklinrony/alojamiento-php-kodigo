<?php

namespace App\Services;

use App\Models\Role;

/**
 * Interface IRoleService
 * Interfaz para el servicio de roles
 */
interface IRoleService
{
    /**
     * Crea un nuevo rol
     *
     * @param array $roleData
     * @return Role
     */
    public function createRole(array $roleData): Role;

    /**
     * Actualiza un rol existente
     *
     * @param int $roleId
     * @param array $roleData
     * @return Role|null
     */
    public function updateRole(int $roleId, array $roleData): ?Role;

    /**
     * Asigna permisos a un rol
     *
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function assignPermissions(int $roleId, array $permissionIds): bool;

    /**
     * Verifica si un rol existe por su nombre
     *
     * @param string $name
     * @return bool
     */
    public function roleExists(string $name): bool;

    /**
     * Obtiene un rol por su ID con sus permisos
     *
     * @param int $roleId
     * @return Role|null
     */
    public function getRoleWithPermissions(int $roleId): ?Role;
}
