<?php

namespace App\Config;

use DI\ContainerBuilder;
use DI\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use App\Utilities\PathHelper;
use App\Utilities\TwigExtensions;
use App\Utilities\RequestValidator;
use App\Utilities\SessionAuthenticator;
use App\Services\Implementations\LoggerService;
use App\Services\Implementations\UserService;
use App\Services\Implementations\RoleService;
use App\Services\Implementations\PermissionService;
use App\Services\Implementations\AccommodationService;
use App\Services\Implementations\ReservationService;
use App\Repositories\Implementations\UserRepository;
use App\Repositories\Implementations\RoleRepository;
use App\Repositories\Implementations\PermissionRepository;
use App\Repositories\Implementations\AccommodationRepository;
use App\Repositories\Implementations\ReservationRepository;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Accommodation;
use App\Models\Reservation;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AccommodationController;
use App\Controllers\UserController;
use App\Controllers\RedirectController;
use App\Controllers\SimpleAdminController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AdminAccommodationController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\PermissionMiddleware;

/**
 * Configuración de PHP-DI para la aplicación
 */
class DiConfig
{
    private static ?Container $container = null;

    /**
     * Obtiene la instancia del contenedor
     */
    public static function getContainer(): Container
    {
        if (self::$container === null) {
            self::$container = self::buildContainer();
        }
        return self::$container;
    }

    /**
     * Construye el contenedor con todas las configuraciones
     */
    private static function buildContainer(): Container
    {
        $builder = new ContainerBuilder();
        
        // Habilitar autowiring para mejor rendimiento
        $builder->useAutowiring(true);
        
        // Configurar cache del contenedor para optimización
        $cacheEnabled = $_ENV['APP_DEBUG'] !== 'true' && $_ENV['DI_CACHE_ENABLED'] !== 'false';
        
        if ($cacheEnabled) {
            $cacheFile = PathHelper::fromRoot('var/cache/di_container.cache');
            $cacheDir = dirname($cacheFile);
            
            // Asegurar que el directorio de cache existe
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            $builder->enableCompilation($cacheDir);
        }
        
        // Configurar definiciones
        $builder->addDefinitions(self::getDefinitions());
        
        return $builder->build();
    }

    /**
     * Obtiene todas las definiciones de servicios
     */
    private static function getDefinitions(): array
    {
        return [
            // ===== UTILIDADES BASE =====
            \App\Utilities\IRequestValidator::class => \DI\create(RequestValidator::class),
            \App\Utilities\IAuthenticator::class => \DI\create(SessionAuthenticator::class)
                ->constructor(\DI\get(\App\Services\IUserService::class)),

            // ===== TWIG CONFIGURACIÓN OPTIMIZADA =====
            Environment::class => \DI\factory(function (Container $container) {
                $loader = new FilesystemLoader(PathHelper::getViewsPath());
                $twig = new Environment($loader, [
                    'cache' => ($_ENV['TWIG_CACHE_ENABLED'] ?? 'false') === 'true' ? __DIR__ . '/../../var/cache/twig' : false,
                    'debug' => $_ENV['APP_DEBUG'] === 'true',
                    'auto_reload' => $_ENV['APP_DEBUG'] === 'true'
                ]);
                
                // Solo agregar debug extension en modo debug
                if ($_ENV['APP_DEBUG'] === 'true') {
                    $twig->addExtension(new DebugExtension());
                }
                
                // Agregar extensiones personalizadas - EVITAR DEPENDENCIA CIRCULAR
                // TwigExtensions se inicializará sin IAuthenticator para evitar el bucle
                $twig->addExtension(new TwigExtensions(null));
                
                return $twig;
            }),

            // ===== SERVICIOS DE LOGGING =====
            \App\Services\ILoggerService::class => \DI\factory(function() {
                return new LoggerService();
            }),

            // ===== MODELOS =====
            User::class => \DI\create(),
            Role::class => \DI\create(),
            Permission::class => \DI\create(),
            Accommodation::class => \DI\create(),
            Reservation::class => \DI\create(),

            // ===== REPOSITORIOS =====
            \App\Repositories\IUserRepository::class => \DI\factory(function(Container $container) {
                return new UserRepository($container->get(User::class));
            }),
            \App\Repositories\IRoleRepository::class => \DI\factory(function(Container $container) {
                return new RoleRepository($container->get(Role::class));
            }),
            \App\Repositories\IPermissionRepository::class => \DI\factory(function(Container $container) {
                return new PermissionRepository($container->get(Permission::class));
            }),
            \App\Repositories\IAccommodationRepository::class => \DI\factory(function(Container $container) {
                return new AccommodationRepository($container->get(Accommodation::class));
            }),
            \App\Repositories\IReservationRepository::class => \DI\factory(function(Container $container) {
                return new ReservationRepository($container->get(Reservation::class));
            }),

            // ===== SERVICIOS =====
            \App\Services\IUserService::class => \DI\factory(function(Container $container) {
                return new UserService(
                    $container->get(\App\Repositories\IUserRepository::class),
                    $container->get(\App\Repositories\IRoleRepository::class),
                    $container->get(\App\Repositories\IPermissionRepository::class)
                );
            }),
            \App\Services\IRoleService::class => \DI\factory(function(Container $container) {
                return new RoleService($container->get(\App\Repositories\IRoleRepository::class));
            }),
            \App\Services\IPermissionService::class => \DI\factory(function(Container $container) {
                return new PermissionService($container->get(\App\Repositories\IPermissionRepository::class));
            }),
            \App\Services\IAccommodationService::class => \DI\factory(function(Container $container) {
                return new AccommodationService(
                    $container->get(\App\Repositories\IAccommodationRepository::class),
                    $container->get(\App\Services\IUserService::class)
                );
            }),
            \App\Services\IReservationService::class => \DI\factory(function(Container $container) {
                return new ReservationService(
                    $container->get(\App\Repositories\IReservationRepository::class),
                    $container->get(\App\Repositories\IAccommodationRepository::class),
                    $container->get(\App\Services\ILoggerService::class)
                );
            }),

            // ===== CONTROLADORES =====
            HomeController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Services\IAccommodationService::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class)
                ),
            AuthController::class => \DI\create()
                ->constructor(
                    \DI\get(\App\Services\IUserService::class),
                    \DI\get(Environment::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class)
                ),
            AccommodationController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Services\IAccommodationService::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class)
                ),
            UserController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Services\IUserService::class),
                    \DI\get(\App\Services\IRoleService::class),
                    \DI\get(\App\Services\IReservationService::class),
                    \DI\get(\App\Services\IAccommodationService::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class),
                    \DI\get(\App\Services\ILoggerService::class)
                ),
            RedirectController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class)
                ),
            SimpleAdminController::class => \DI\create(),
            DashboardController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class)
                ),
            AdminAccommodationController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Services\IAccommodationService::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class)
                ),
            \App\Controllers\Admin\AccommodationController::class => \DI\create()
                ->constructor(
                    \DI\get(Environment::class),
                    \DI\get(\App\Utilities\IRequestValidator::class),
                    \DI\get(\App\Utilities\IAuthenticator::class),
                    \DI\get(\App\Services\IAccommodationService::class),
                    \DI\get(\App\Services\IUserService::class)
                ),

            // ===== MIDDLEWARES =====
            CorsMiddleware::class => \DI\create(),
            AuthMiddleware::class => \DI\create()
                ->constructor(\DI\get(\App\Utilities\IAuthenticator::class)),
            PermissionMiddleware::class => \DI\create()
                ->constructor(
                    \DI\get(\App\Services\IUserService::class),
                    \DI\get(\App\Services\IRoleService::class),
                    \DI\get(\App\Services\IPermissionService::class)
                ),
        ];
    }

    /**
     * Limpia el cache del contenedor DI
     * Útil para desarrollo cuando se modifican las definiciones
     *
     * @return bool
     */
    public static function clearCache(): bool
    {
        $cacheDir = PathHelper::fromRoot('var/cache');
        
        if (!is_dir($cacheDir)) {
            return true; // No existe el directorio, consideramos que está "limpio"
        }
        
        $success = true;
        $files = glob($cacheDir . '/CompiledContainer.php');
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $success = $success && unlink($file);
            }
        }
        
        return $success;
    }

    /**
     * Verifica si el cache del contenedor DI existe
     *
     * @return bool
     */
    public static function cacheExists(): bool
    {
        $cacheDir = PathHelper::fromRoot('var/cache');
        $files = glob($cacheDir . '/CompiledContainer.php');
        return !empty($files);
    }

    /**
     * Obtiene información sobre el cache del contenedor DI
     *
     * @return array
     */
    public static function getCacheInfo(): array
    {
        $cacheDir = PathHelper::fromRoot('var/cache');
        $files = glob($cacheDir . '/CompiledContainer.php');
        
        if (empty($files)) {
            return [
                'exists' => false,
                'files' => [],
                'totalSize' => 0,
                'path' => $cacheDir
            ];
        }
        
        $totalSize = 0;
        $fileInfo = [];
        
        foreach ($files as $file) {
            $size = filesize($file);
            $totalSize += $size;
            $fileInfo[] = [
                'name' => basename($file),
                'size' => $size,
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
                'path' => $file
            ];
        }
        
        return [
            'exists' => true,
            'files' => $fileInfo,
            'totalSize' => $totalSize,
            'path' => $cacheDir
        ];
    }

    /**
     * Resetea la instancia del contenedor
     * Útil para testing o cuando se necesita forzar la recreación
     */
    public static function reset(): void
    {
        self::$container = null;
    }
}
