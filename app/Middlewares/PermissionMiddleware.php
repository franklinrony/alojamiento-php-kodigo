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
        session_start();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'error' => true,
                'message' => 'No autorizado'
            ]);
            return;
        }

        if (!$this->userService->hasPermission($_SESSION['user_id'], $this->permission)) {
            http_response_code(403);
            echo json_encode([
                'error' => true,
                'message' => 'No tiene permisos suficientes'
            ]);
            return;
        }

        $next();
    }
}
