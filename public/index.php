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

// Inicializar y ejecutar el router
try {
    $router = new Router($container);
    $router->dispatch();
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal Server Error'
    ]);
}
