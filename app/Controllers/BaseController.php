<?php

namespace App\Controllers;

use App\Utilities\IAuthenticator;
use App\Utilities\IRequestValidator;

/**
 * Class BaseController
 * Controlador base con funcionalidad común
 */
abstract class BaseController implements IController
{
    /**
     * @var IAuthenticator|null
     */
    protected ?IAuthenticator $authenticator = null;

    /**
     * @var IRequestValidator|null
     */
    protected ?IRequestValidator $validator = null;

    /**
     * @var \Twig\Environment
     */
    protected \Twig\Environment $twig;

    /**
     * Constructor base que configura Twig y otros servicios comunes
     *
     * @param \Twig\Environment $twig
     * @param IRequestValidator|null $validator
     * @param IAuthenticator|null $authenticator
     */
    public function __construct(
        \Twig\Environment $twig,
        ?IRequestValidator $validator = null,
        ?IAuthenticator $authenticator = null
    ) {
        $this->twig = $twig;
        $this->validator = $validator;
        $this->authenticator = $authenticator;
        
        // Configurar variables globales para todas las vistas
        $this->twig->addGlobal('app_name', $_ENV['APP_NAME'] ?? 'Alojamientos Kodigo');
        $this->twig->addGlobal('app_env', $_ENV['APP_ENV'] ?? 'dev');
        $this->twig->addGlobal('is_debug', $_ENV['APP_DEBUG'] ?? false);
        
        $isAuthenticated = $this->authenticator ? $this->authenticator->isAuthenticated() : false;
        $user = $this->authenticator ? $this->authenticator->getUser() : null;
        
        // Debug info
<<<<<<< HEAD
<<<<<<< Updated upstream
        error_log('Auth Status: ' . ($isAuthenticated ? 'true' : 'false'));
        error_log('Session Status: ' . session_status());
        error_log('Session ID: ' . session_id());
        error_log('User in Session: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'none'));
=======
=======
>>>>>>> temp
        // Log de autenticación usando el sistema de logging (solo en modo debug)
        if ($_ENV['APP_DEBUG'] === 'true' && class_exists('\App\Services\ILoggerService')) {
            try {
                $container = \App\Utilities\DiContainer::getInstance();
                $logger = $container->get(\App\Services\ILoggerService::class);
                $logger->debug("Auth status check", [
                    'authenticator_is_null' => $this->authenticator === null,
                    'is_authenticated' => $isAuthenticated,
                    'session_status' => session_status(),
                    'session_id' => session_id(),
                    'user_in_session' => $_SESSION['user_id'] ?? 'none',
                    'user_role_in_session' => $_SESSION['user_role'] ?? 'none',
                    'permissions_in_session' => $_SESSION['permissions'] ?? []
                ]);
            } catch (\Exception $logError) {
                // Fallback silencioso para evitar errores en el logging
            }
        }
<<<<<<< HEAD
>>>>>>> Stashed changes
=======
>>>>>>> temp
        
        // Pasar el authenticator completo para que tenga acceso a hasPermission
        $this->twig->addGlobal('auth', $this->authenticator);
        
        // También pasar user directamente para compatibilidad con layouts que usan 'user'
        $this->twig->addGlobal('user', $user);
        
        // Generar token CSRF para formularios
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->twig->addGlobal('csrf_token', $_SESSION['csrf_token']);
        
        // Configurar variable flash messages si existen en la sesión
        $flash = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        $this->twig->addGlobal('flash', $flash);
    }

    /**
     * @inheritDoc
     */
    public function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Obtiene el contenido JSON del cuerpo de la petición y lo valida
     *
     * @param array $rules Reglas de validación
     * @return array
     * @throws \RuntimeException Si los datos no son válidos
     */
    protected function getJsonRequest(array $rules = []): array
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true) ?? [];

        if (!empty($rules) && $this->validator) {
            if (!$this->validator->validate($data, $rules)) {
                throw new \RuntimeException($this->validator->getErrors()[0]);
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function error(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse([
            'error' => true,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Verifica si el método de la petición coincide con el esperado
     *
     * @param string $method
     * @return bool
     */
    protected function isMethod(string $method): bool
    {
        return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
    }

    /**
     * Obtiene un parámetro de la URL
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getParam(string $name, $default = null)
    {
        return $_GET[$name] ?? $default;
    }

    /**
     * Verifica si una petición está autenticada
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Obtiene el ID del usuario autenticado
     *
     * @return int|null
     */
    protected function getAuthUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Agrega un mensaje flash para la siguiente petición
     *
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message El mensaje a mostrar
     */
    protected function flash(string $type, string $message): void
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        if (!isset($_SESSION['flash_messages'][$type])) {
            $_SESSION['flash_messages'][$type] = [];
        }
        $_SESSION['flash_messages'][$type][] = $message;
    }

    /**
     * Determina si la petición es una petición API basándose en los encabezados
     *
     * @return bool
     */
    protected function isApiRequest(): bool
    {
        return isset($_SERVER['HTTP_ACCEPT']) && 
               (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ||
                strpos($_SERVER['HTTP_CONTENT_TYPE'] ?? '', 'application/json') !== false);
    }

    /**
     * Envía una respuesta JSON exitosa
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public function success($data, int $statusCode = 200): void
    {
        $this->jsonResponse([
            'success' => true,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Renderiza una vista usando Twig con los datos proporcionados
     *
     * @param string $template
     * @param array $data
     * @return void
     * @throws \RuntimeException Si Twig no está inicializado
     */
    private static $hasRendered = false;

    protected function render(string $template, array $data = []): void
    {
        if (self::$hasRendered) {
            return;
        }

        if (!isset($this->twig)) {
            throw new \RuntimeException('Twig environment not initialized');
        }

        // Asegurar que tengamos valores por defecto
        $data['app_name'] = $_ENV['APP_NAME'] ?? 'Alojamientos Kodigo';
        $data['app_env'] = $_ENV['APP_ENV'] ?? 'production';
        $data['is_debug'] = $_ENV['APP_DEBUG'] === 'true';
        
        self::$hasRendered = true;
        $output = $this->twig->render($template, $data);
        echo $output;
        exit;
    }
}