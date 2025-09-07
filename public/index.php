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
} catch (\Exception $e) {
    error_log("Error en la aplicación: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal Server Error'
    ]);
}
