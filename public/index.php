<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Utilities\Router;
use App\Utilities\DiContainer;
use App\Utilities\PathHelper;
use App\Utilities\ErrorHandler;
use App\Services\ILoggerService;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(PathHelper::projectRoot());
$dotenv->load();

// Configurar manejo de errores básico
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? '1');

// Configurar sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

// Iniciar sesión
session_start();

// Obtener el contenedor de dependencias PHP-DI
$container = DiContainer::getInstance();

// Inicializar sistema de logging y manejo de errores
try {
    $logger = $container->get(ILoggerService::class);
    ErrorHandler::initialize($container);
    
    $logger->info("=== Nueva solicitud ===", [
        'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
} catch (\Exception $e) {
    // Fallback a error_log si el sistema de logging falla
    error_log("Error inicializando logging: " . $e->getMessage());
    error_log("=== Nueva solicitud ===");
    error_log("URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
    error_log("Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
}

// Inicializar y ejecutar el router
try {
    static $routerInstance = null;
    if ($routerInstance === null) {
        $routerInstance = new Router($container);
    }
    $routerInstance->dispatch();
} catch (\App\Exceptions\DatabaseConnectionException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    
    if (\App\Utilities\ErrorRenderer::isApiRequest()) {
        \App\Utilities\ErrorRenderer::renderJsonError(
            'Servicio temporalmente no disponible. Error de conexión a la base de datos.',
            503
        );
    } else {
        \App\Utilities\ErrorRenderer::renderErrorPage($container, 'errors/database.twig', [
            'pageTitle' => 'Error de Conexión',
            'error' => $e->getMessage(),
            'debug' => $_ENV['APP_DEBUG'] ?? false
        ]);
    }
} catch (\Exception $e) {
    error_log("Error en la aplicación: " . $e->getMessage());
    
    if (\App\Utilities\ErrorRenderer::isApiRequest()) {
        \App\Utilities\ErrorRenderer::renderJsonError(
            $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal Server Error',
            500
        );
    } else {
        \App\Utilities\ErrorRenderer::renderErrorPage($container, 'errors/500.twig', [
            'pageTitle' => 'Error del Servidor',
            'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Ha ocurrido un error interno del servidor',
            'debug' => $_ENV['APP_DEBUG'] ?? false
        ]);
    }
}

// Limpiar recursos al final del request para evitar memory leaks
register_shutdown_function(function() {
    \App\Utilities\DiContainer::cleanup();
});