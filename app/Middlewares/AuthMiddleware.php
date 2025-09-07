<?php

namespace App\Middlewares;

/**
 * Class AuthMiddleware
 * Middleware para verificar autenticación
 */
class AuthMiddleware implements IMiddleware
{
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

        $next();
    }
}
