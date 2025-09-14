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
            \App\Services\ILoggerService::class => \DI\create(LoggerService::class),

            // ===== MODELOS =====
            User::class => \DI\create(),
            Role::class => \DI\create(),
            Permission::class => \DI\create(),
            Accommodation::class => \DI\create(),
            Reservation::class => \DI\create(),

            // ===== REPOSITORIOS =====
            \App\Repositories\IUserRepository::class => \DI\create(UserRepository::class)
                ->constructor(\DI\get(User::class)),
            \App\Repositories\IRoleRepository::class => \DI\create(RoleRepository::class)
                ->constructor(\DI\get(Role::class)),
            \App\Repositories\IPermissionRepository::class => \DI\create(PermissionRepository::class)
                ->constructor(\DI\get(Permission::class)),
            \App\Repositories\IAccommodationRepository::class => \DI\create(AccommodationRepository::class)
                ->constructor(\DI\get(Accommodation::class)),
            \App\Repositories\IReservationRepository::class => \DI\create(ReservationRepository::class)
                ->constructor(\DI\get(Reservation::class)),

            // ===== SERVICIOS =====
            \App\Services\IUserService::class => \DI\create(UserService::class)
                ->constructor(
                    \DI\get(\App\Repositories\IUserRepository::class),
                    \DI\get(\App\Repositories\IRoleRepository::class),
                    \DI\get(\App\Repositories\IPermissionRepository::class)
                ),
            \App\Services\IRoleService::class => \DI\create(RoleService::class)
                ->constructor(\DI\get(\App\Repositories\IRoleRepository::class)),
            \App\Services\IPermissionService::class => \DI\create(PermissionService::class)
                ->constructor(\DI\get(\App\Repositories\IPermissionRepository::class)),
            \App\Services\IAccommodationService::class => \DI\create(AccommodationService::class)
                ->constructor(
                    \DI\get(\App\Repositories\IAccommodationRepository::class),
                    \DI\get(\App\Services\IUserService::class)
                ),
            \App\Services\IReservationService::class => \DI\create(ReservationService::class)
                ->constructor(
                    \DI\get(\App\Repositories\IReservationRepository::class),
                    \DI\get(\App\Repositories\IAccommodationRepository::class),
                    \DI\get(\App\Services\ILoggerService::class)
                ),

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
}
