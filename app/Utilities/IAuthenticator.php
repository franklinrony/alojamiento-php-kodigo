<?php

namespace App\Utilities;

/**
 * Interface IAuthenticator
 * Interfaz para el manejo de autenticación
 */
interface IAuthenticator
{
    /**
     * Autentica un usuario
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function authenticate(string $email, string $password): bool;

    /**
     * Obtiene el ID del usuario autenticado
     *
     * @return int|null
     */
    public function getUserId(): ?int;

    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void;

    /**
     * Verifica si hay un usuario autenticado
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Obtiene los datos del usuario autenticado
     *
     * @return array|null
     */
    public function getAuthenticatedUser(): ?array;
}
