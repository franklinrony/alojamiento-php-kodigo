<?php

namespace App\Services;

use App\Models\User;

/**
 * Interface IUserService
 * Interfaz para el servicio de usuarios
 */
interface IUserService
{
    /**
     * Registra un nuevo usuario
     *
     * @param array $userData
     * @return User
     */
    public function register(array $userData): User;

    /**
     * Intenta autenticar un usuario
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function login(string $email, string $password): ?User;

    /**
     * Actualiza la información de un usuario
     *
     * @param int $userId
     * @param array $userData
     * @return User|null
     */
    public function updateUser(int $userId, array $userData): ?User;

    /**
     * Asigna un rol a un usuario
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function assignRole(int $userId, int $roleId): bool;

    /**
     * Verifica si un usuario tiene un permiso específico
     *
     * @param int $userId
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(int $userId, string $permissionName): bool;
}
