<?php

namespace App\Middlewares;

use App\Services\IUserService;

/**
 * Class PermissionMiddleware
 * Middleware para verificar permisos
 */
class PermissionMiddleware implements IMiddleware
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var string
     */
    private $permission;

    /**
     * Determina si la petición es una API request
     */
    private function isApiRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
            strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    }

    /**
     * @param IUserService $userService
     * @param string $permission
     */
    public function __construct(IUserService $userService, string $permission)
    {
        $this->userService = $userService;
        $this->permission = $permission;
    }

    /**
     * @inheritDoc
     */
    public function handle(callable $next): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            if ($this->isApiRequest()) {
                http_response_code(401);
                echo json_encode([
                    'error' => true,
                    'message' => 'No autorizado'
                ]);
            } else {
                header('Location: /auth/login');
            }
            return;
        }

        if (!$this->userService->hasPermission($_SESSION['user_id'], $this->permission)) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                echo json_encode([
                    'error' => true,
                    'message' => 'No tiene permisos suficientes'
                ]);
            } else {
                header('Location: /error/403');
            }
            return;
        }

        $next();
    }
}
