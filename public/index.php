<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Accommodation;
use App\Repositories\Implementations\UserRepository;
use App\Repositories\Implementations\RoleRepository;
use App\Repositories\Implementations\PermissionRepository;
use App\Repositories\Implementations\AccommodationRepository;
use App\Services\Implementations\UserService;
use App\Services\Implementations\RoleService;
use App\Services\Implementations\PermissionService;
use App\Services\Implementations\AccommodationService;
use App\Utilities\Router;

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? '0');

// Iniciar sesión
session_start();

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Si es una petición OPTIONS, terminar aquí (pre-flight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Crear instancias de modelos
$userModel = new User();
$roleModel = new Role();
$permissionModel = new Permission();
$accommodationModel = new Accommodation();

// Crear instancias de repositorios
$userRepository = new UserRepository($userModel);
$roleRepository = new RoleRepository($roleModel);
$permissionRepository = new PermissionRepository($permissionModel);
$accommodationRepository = new AccommodationRepository($accommodationModel);

// Crear instancias de servicios
$roleService = new RoleService($roleRepository);
$permissionService = new PermissionService($permissionRepository);
$userService = new UserService($userRepository, $roleRepository);
$accommodationService = new AccommodationService($accommodationRepository, $userService);

// Configurar contenedor de dependencias
$container = [
    \App\Services\IUserService::class => $userService,
    \App\Services\IRoleService::class => $roleService,
    \App\Services\IPermissionService::class => $permissionService,
    \App\Services\IAccommodationService::class => $accommodationService,
];

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
