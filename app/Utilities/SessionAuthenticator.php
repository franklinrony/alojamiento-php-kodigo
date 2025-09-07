<?php

namespace App\Utilities;

use App\Services\IUserService;
use App\Models\User;

/**
 * Class SessionAuthenticator
 * Implementación de autenticación basada en sesiones
 */
class SessionAuthenticator implements IAuthenticator
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @param IUserService $userService
     */
    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(string $email, string $password): bool
    {
        try {
            $user = $this->userService->login($email, $password);
            
            if (!$user) {
                return false;
            }

            // Iniciar sesión y guardar datos básicos
            $this->startSession($user);
            
            return true;
        } catch (\RuntimeException $e) {
            // Propagar excepciones específicas (como cuenta inactiva)
            throw $e;
        } catch (\Exception $e) {
            // Cualquier otro error se maneja como fallo de autenticación
            return false;
        }
    }

    /**
     * Inicia la sesión con los datos del usuario
     * 
     * @param User $user
     */
    private function startSession(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['user_role'] = $user->getRoleId();
        $_SESSION['last_activity'] = time();
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
            session_regenerate_id(true);
        }
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        if (!isset($_SESSION['user_id'], $_SESSION['last_activity'])) {
            return false;
        }

        // Verificar tiempo de inactividad (30 minutos)
        if (time() - $_SESSION['last_activity'] > 1800) {
            $this->logout();
            return false;
        }

        // Actualizar tiempo de última actividad
        $_SESSION['last_activity'] = time();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getAuthenticatedUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $user = $this->userService->getUser($_SESSION['user_id']);
            if (!$user || !$user->isActive()) {
                $this->logout();
                return null;
            }

            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'role_id' => $user->getRoleId(),
                'is_active' => $user->isActive()
            ];
        } catch (\Exception $e) {
            $this->logout();
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?User
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $user = $this->userService->getUser($_SESSION['user_id']);
            if (!$user || !$user->isActive()) {
                $this->logout();
                return null;
            }
            return $user;
        } catch (\Exception $e) {
            $this->logout();
            return null;
        }
    }
}
