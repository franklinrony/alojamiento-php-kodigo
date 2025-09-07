<?php

namespace App\Middlewares;

/**
 * Class AuthMiddleware
 * Middleware para verificar autenticación
 */
class AuthMiddleware implements IMiddleware
{
    /**
     * @var \App\Utilities\IAuthenticator
     */
    private $authenticator;

    /**
     * @param \App\Utilities\IAuthenticator $authenticator
     */
    public function __construct(\App\Utilities\IAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @inheritDoc
     */
    public function handle(callable $next): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            // Si es una petición AJAX o API
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
                strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
                strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                http_response_code(401);
                echo json_encode([
                    'error' => true,
                    'message' => 'No autorizado'
                ]);
                return;
            }

            // Si es una petición web normal, redirigir al login
            header('Location: /auth/login');
            exit;
        }

        $next();
    }
}
