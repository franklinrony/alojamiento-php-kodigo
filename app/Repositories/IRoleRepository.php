<?php

namespace App\Repositories;

/**
 * Interface IRoleRepository
 * Interfaz para el repositorio de roles
 */
interface IRoleRepository extends IRepository
{
    /**
     * Encuentra un rol por su nombre
     *
     * @param string $name
     * @return mixed
     */
    public function findByName(string $name);

    /**
     * Obtiene los permisos asociados a un rol
     *
     * @param int $roleId
     * @return array
     */
    public function getPermissions(int $roleId);

    /**
     * Asigna permisos a un rol
     *
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function assignPermissions(int $roleId, array $permissionIds);
}
