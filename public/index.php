<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Utilities\Router;
use App\Utilities\ContainerBuilder;
use App\Utilities\PathHelper;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(PathHelper::projectRoot());
$dotenv->load();

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? '0');

// Iniciar sesión
session_start();

// Obtener el contenedor de dependencias
$container = ContainerBuilder::getInstance();

// Debug info
error_log("=== Nueva solicitud ===");
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Method: " . $_SERVER['REQUEST_METHOD']);

// Inicializar y ejecutar el router
try {
    static $routerInstance = null;
    if ($routerInstance === null) {
        error_log("Creando nueva instancia del Router");
        $routerInstance = new Router($container);
    } else {
        error_log("Reutilizando instancia existente del Router");
    }
    $routerInstance->dispatch();
} catch (\App\Exceptions\DatabaseConnectionException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    
    if (\App\Utilities\ErrorRenderer::isApiRequest()) {
        // Para peticiones API, devolver JSON
        \App\Utilities\ErrorRenderer::renderJsonError(
            'Servicio temporalmente no disponible. Error de conexión a la base de datos.',
            503
        );
    } else {
        // Para peticiones web, mostrar una página de error específica para BD
        \App\Utilities\ErrorRenderer::renderErrorPage($container, 'errors/database.twig', [
            'pageTitle' => 'Error de Conexión',
            'error' => $e->getMessage(),
            'debug' => $_ENV['APP_DEBUG'] ?? false
        ]);
    }
} catch (\Exception $e) {
    error_log("Error en la aplicación: " . $e->getMessage());
    
    if (\App\Utilities\ErrorRenderer::isApiRequest()) {
        // Para peticiones API, devolver JSON
        \App\Utilities\ErrorRenderer::renderJsonError(
            $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal Server Error',
            500
        );
    } else {
        // Para peticiones web, mostrar una página de error
        \App\Utilities\ErrorRenderer::renderErrorPage($container, 'errors/500.twig', [
            'pageTitle' => 'Error del Servidor',
            'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Ha ocurrido un error interno del servidor',
            'debug' => $_ENV['APP_DEBUG'] ?? false
        ]);
    }
}

