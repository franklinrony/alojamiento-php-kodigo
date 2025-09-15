<?php

namespace App\Middlewares;

/**
 * Class CorsMiddleware
 * Middleware para manejar CORS
 */
class CorsMiddleware implements IMiddleware
{
    /**
     * @inheritDoc
     */
    public function handle(callable $next): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Si es una petición OPTIONS, terminar aquí
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }

        $next();
    }
}
