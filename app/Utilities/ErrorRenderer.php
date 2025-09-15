<?php

namespace App\Utilities;

use DI\Container;

class ErrorRenderer
{
    /**
     * Renderiza una página de error usando Twig
     */
    public static function renderErrorPage(Container $container, string $template, array $data = []): void
    {
        try {
            $twig = $container->get(\Twig\Environment::class);
            
            // Configurar variables globales para páginas de error
            self::configureTwigGlobals($container, $twig);
            
            echo $twig->render($template, $data);
        } catch (\Exception $twigError) {
            // Si incluso el renderizado de Twig falla, usar fallback
            self::renderFallbackError($data);
        }
    }

    /**
     * Renderiza un error de fallback usando un template básico
     */
    public static function renderFallbackError(array $data = []): void
    {
        try {
            // Intentar obtener el contenedor para configurar Twig con variables globales
            $container = null;
            try {
                $container = \App\Utilities\DiContainer::getInstance();
            } catch (\Exception $e) {
                // Si no se puede obtener el contenedor, continuar sin él
            }
            
            // Intentar usar el template de fallback
            $fallbackData = [
                'errorCode' => '500',
                'errorTitle' => $data['pageTitle'] ?? 'Error del Servidor',
                'errorMessage' => $data['error'] ?? 'Ha ocurrido un error interno del servidor',
                'errorDetails' => $data['debug'] ? ($data['error'] ?? '') : null,
                'debug' => $data['debug'] ?? false
            ];
            
            if ($container) {
                // Usar el Twig del contenedor si está disponible
                try {
                    $twig = $container->get(\Twig\Environment::class);
                    self::configureTwigGlobals($container, $twig);
                    echo $twig->render('errors/fallback.twig', $fallbackData);
                    return;
                } catch (\Exception $e) {
                    // Si falla, continuar con Twig básico
                }
            }
            
            // Crear un Twig básico para el fallback
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../app/Views');
            $twig = new \Twig\Environment($loader, ['debug' => false]);
            echo $twig->render('errors/fallback.twig', $fallbackData);
        } catch (\Exception $fallbackError) {
            // Último recurso: HTML básico
            self::renderBasicHtmlError($data);
        }
    }

    /**
     * Renderiza HTML básico como último recurso
     */
    private static function renderBasicHtmlError(array $data = []): void
    {
        $title = $data['pageTitle'] ?? 'Error del Servidor';
        $message = $data['error'] ?? 'Ha ocurrido un error interno del servidor';
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>' . htmlspecialchars($title) . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    text-align: center; 
                    padding: 50px; 
                    background-color: #f8fafc;
                }
                .error { 
                    color: #d32f2f; 
                    font-size: 2rem;
                    margin-bottom: 1rem;
                }
                .message {
                    color: #64748b;
                    margin-bottom: 2rem;
                }
                .btn {
                    display: inline-block;
                    padding: 0.5rem 1rem;
                    background-color: #2563eb;
                    color: white;
                    text-decoration: none;
                    border-radius: 0.25rem;
                    margin: 0.25rem;
                }
                .btn:hover {
                    background-color: #1d4ed8;
                }
            </style>
        </head>
        <body>
            <h1 class="error">' . htmlspecialchars($title) . '</h1>
            <p class="message">' . htmlspecialchars($message) . '</p>
            <a href="/" class="btn">Volver al Inicio</a>
        </body>
        </html>';
    }

    /**
     * Configura las variables globales de Twig para páginas de error
     */
    private static function configureTwigGlobals(Container $container, \Twig\Environment $twig): void
    {
        try {
            // Configurar variables básicas de la aplicación
            $twig->addGlobal('app_name', $_ENV['APP_NAME'] ?? 'Alojamientos Kodigo');
            $twig->addGlobal('app_env', $_ENV['APP_ENV'] ?? 'dev');
            $twig->addGlobal('is_debug', $_ENV['APP_DEBUG'] ?? false);
            
            // Intentar obtener el authenticator del contenedor
            $authenticator = null;
            try {
                $authenticator = $container->get(\App\Utilities\IAuthenticator::class);
            } catch (\Exception $e) {
                // Si no se puede obtener el authenticator, continuar sin él
            }
            
            // Configurar autenticación
            $twig->addGlobal('auth', $authenticator);
            
            // Obtener usuario si está autenticado
            $user = null;
            if ($authenticator && $authenticator->isAuthenticated()) {
                $user = $authenticator->getUser();
            }
            $twig->addGlobal('user', $user);
            
            // Configurar token CSRF
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $twig->addGlobal('csrf_token', $_SESSION['csrf_token']);
            
            // Configurar flash messages
            $flash = $_SESSION['flash_messages'] ?? [];
            unset($_SESSION['flash_messages']);
            $twig->addGlobal('flash', $flash);
            
        } catch (\Exception $e) {
            // Si hay algún error configurando las variables globales, continuar sin ellas
            // Esto evita que un error en la configuración rompa el renderizado de la página de error
        }
    }

    /**
     * Determina si la petición actual es una petición API
     */
    public static function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0 || 
               strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }

    /**
     * Renderiza una respuesta JSON de error para APIs
     */
    public static function renderJsonError(string $message, int $httpCode = 500): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message
        ]);
    }
}
