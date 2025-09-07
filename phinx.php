<?php


// 1. Asegúrate de que el autoload de Composer esté incluido
require_once __DIR__ . '/vendor/autoload.php';

// 2. Define la ruta al directorio raíz del proyecto
$baseDir = __DIR__;

// 3. Carga el archivo .env desde el directorio raíz
$dotenv = Dotenv\Dotenv::createImmutable($baseDir);
$dotenv->load();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/config/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/config/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
        'adapter' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'alojamientos',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'charset' => 'utf8',
        ],
        'development' => [
        'adapter' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'alojamientos',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'charset' => 'utf8',
        ],
        'testing' => [
        'adapter' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'alojamientos',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
