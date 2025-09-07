<?php

namespace App\Repositories;

/**
 * Interface PermissionRepositoryInterface
 * Interfaz para el repositorio de permisos
 */
interface PermissionRepositoryInterface extends RepositoryInterface
{
    /**
     * Encuentra un permiso por su nombre
     *
     * @param string $name
     * @return mixed
     */
    public function findByName(string $name);

    /**
     * Obtiene todos los roles que tienen este permiso
     *
     * @param int $permissionId
     * @return array
     */
    public function getRoles(int $permissionId);
}
