<?php

namespace App\Utilities;

use League\Container\Container;

class ErrorRenderer
{
    /**
     * Renderiza una página de error usando Twig
     */
    public static function renderErrorPage(Container $container, string $template, array $data = []): void
    {
        try {
            if ($container->has(\Twig\Environment::class)) {
                $twig = $container->get(\Twig\Environment::class);
                echo $twig->render($template, $data);
            } else {
                // Fallback si Twig no está disponible
                self::renderFallbackError($data);
            }
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
            // Intentar usar el template de fallback
            $fallbackData = [
                'errorCode' => '500',
                'errorTitle' => $data['pageTitle'] ?? 'Error del Servidor',
                'errorMessage' => $data['error'] ?? 'Ha ocurrido un error interno del servidor',
                'errorDetails' => $data['debug'] ? ($data['error'] ?? '') : null,
                'debug' => $data['debug'] ?? false
            ];
            
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
