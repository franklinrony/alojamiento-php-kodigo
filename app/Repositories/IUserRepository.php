<?php

namespace App\Repositories;

use App\Models\User;

/**
 * Interface IUserRepository
 * Interfaz para el repositorio de usuarios
 */
interface IUserRepository extends IRepository
{
    /**
     * Encuentra un usuario por su email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email);

    /**
     * Obtiene todos los usuarios por rol
     *
     * @param int $roleId
     * @return array
     */
    public function findByRole(int $roleId);

    /**
     * Verifica si existe un usuario con el email dado
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email);
}
