<?php

namespace App\Utilities;

use League\Container\Container;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Accommodation;
use App\Models\Reservation;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AccommodationController;
use App\Controllers\RedirectController;
// use App\Controllers\TestController; // No existe
use App\Controllers\SimpleAdminController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AdminAccommodationController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\PermissionMiddleware;
use App\Utilities\PathHelper;
use App\Repositories\Implementations\UserRepository;
use App\Repositories\Implementations\RoleRepository;
use App\Repositories\Implementations\PermissionRepository;
use App\Repositories\Implementations\AccommodationRepository;
use App\Repositories\Implementations\ReservationRepository;
use App\Services\Implementations\UserService;
use App\Services\Implementations\RoleService;
use App\Services\Implementations\PermissionService;
use App\Services\Implementations\AccommodationService;
use App\Services\Implementations\ReservationService;
use App\Services\Implementations\LoggerService;
use App\Services\IUserService;
use App\Services\IRoleService;
use App\Services\IPermissionService;
use App\Services\IAccommodationService;
use App\Services\IReservationService;
use App\Services\ILoggerService;
use App\Repositories\IUserRepository;
use App\Repositories\IRoleRepository;
use App\Repositories\IPermissionRepository;
use App\Repositories\IAccommodationRepository;
use App\Repositories\IReservationRepository;

class ContainerBuilder
{
    private static ?Container $instance = null;

    public static function getInstance(): Container
    {
        static $initialized = false;
        if (self::$instance === null || !$initialized) {
            self::$instance = self::buildContainer();
            $initialized = true;
        }
        return self::$instance;
    }

    private static function buildContainer(): Container
    {
        $container = new Container();

        // Solo registrar lo esencial
        self::registerBaseUtilities($container);
        self::registerBaseServices($container);
        self::registerAuthModule($container);
        
        // El resto se registra bajo demanda
        return $container;
    }

    private static function registerBaseUtilities(Container $container): void
    {
        // Registrar validador de peticiones
        $container->addShared(IRequestValidator::class, RequestValidator::class);

        // Registrar autenticador
        $container->addShared(IAuthenticator::class, SessionAuthenticator::class)
            ->addArgument(IUserService::class);

        // Configurar Twig como singleton
        $container->addShared(\Twig\Environment::class, function () use ($container) {
            $loader = new \Twig\Loader\FilesystemLoader(PathHelper::getViewsPath());
            $twig = new \Twig\Environment($loader, [
                'cache' => $_ENV['APP_DEBUG'] === 'true' ? false : __DIR__ . '/../../var/cache/twig',
                'debug' => $_ENV['APP_DEBUG'] === 'true',
                'auto_reload' => $_ENV['APP_DEBUG'] === 'true'
            ]);
            
            // Si estamos en modo debug, agregar la extensión de debug de Twig
            if ($_ENV['APP_DEBUG'] === 'true') {
                $twig->addExtension(new \Twig\Extension\DebugExtension());
            }

            // Agregar nuestras extensiones personalizadas
            $twig->addExtension(new TwigExtensions(
                $container->get(IAuthenticator::class)
            ));
            
            return $twig;
        });
    }

    /**
     * Registra módulos bajo demanda para optimizar rendimiento
     */
    public static function ensureModuleRegistered(string $module): void
    {
        $container = self::getInstance();
        
        switch ($module) {
            case 'accommodation':
                if (!$container->has(IAccommodationService::class)) {
                    self::registerAccommodationModule($container);
                }
                break;
            case 'reservation':
                if (!$container->has(IReservationService::class)) {
                    self::registerReservationModule($container);
                }
                break;
            case 'controllers':
                if (!$container->has(HomeController::class)) {
                    self::registerControllers($container);
                }
                break;
            case 'middlewares':
                if (!$container->has(CorsMiddleware::class)) {
                    self::registerMiddlewares($container);
                }
                break;
        }
    }

    private static function registerControllers(Container $container): void
    {
        // Registrar HomeController
        $container->add(HomeController::class)
            ->addArguments([
                \Twig\Environment::class,
                IAccommodationService::class,
                IRequestValidator::class,
                IAuthenticator::class
            ]);

        // Registrar UserController
        $container->add(\App\Controllers\UserController::class)
            ->addArguments([
                \Twig\Environment::class,
                IUserService::class,
                IRoleService::class,
                IReservationService::class,
                IAccommodationService::class,
                IRequestValidator::class,
                IAuthenticator::class,
                ILoggerService::class
            ]);

        // Registrar AuthController
        $container->add(AuthController::class)
            ->addArgument(IUserService::class)
            ->addArgument(\Twig\Environment::class)
            ->addArgument(IRequestValidator::class)
            ->addArgument(IAuthenticator::class);

        // Registrar AccommodationController
        $container->add(AccommodationController::class)
            ->addArgument(\Twig\Environment::class)
            ->addArgument(IAccommodationService::class)
            ->addArgument(IRequestValidator::class)
            ->addArgument(IAuthenticator::class);

        // Registrar DashboardController (Admin)
        $container->add(DashboardController::class)
            ->addArguments([
                \Twig\Environment::class,
                IRequestValidator::class,
                IAuthenticator::class
            ]);

        // Registrar AdminAccommodationController (controlador anterior)
        $container->add(AdminAccommodationController::class)
            ->addArguments([
                \Twig\Environment::class,
                IAccommodationService::class,
                IRequestValidator::class,
                IAuthenticator::class
            ]);

        // Registrar AdminAccommodationControllerNew (nuevo controlador)
        $container->add('App\Controllers\Admin\AccommodationController')
            ->addArguments([
                \Twig\Environment::class,
                IRequestValidator::class,
                IAuthenticator::class,
                IAccommodationService::class,
                IUserService::class
            ]);

        // Registrar RedirectController
        $container->add(RedirectController::class)
            ->addArguments([
                \Twig\Environment::class,
                IRequestValidator::class,
                IAuthenticator::class
            ]);

        // Registrar TestController (sin dependencias) - comentado porque no existe
        // $container->add(TestController::class);

        // Registrar SimpleAdminController (sin dependencias)
        $container->add(SimpleAdminController::class);
    }

    private static function registerMiddlewares(Container $container): void
    {
        // CORS middleware no requiere dependencias
        $container->add(CorsMiddleware::class);

        // Auth middleware necesita el autenticador
        $container->add(AuthMiddleware::class)
            ->addArgument(IAuthenticator::class);

        // Permission middleware necesita servicios de roles y permisos
        $container->add(PermissionMiddleware::class)
            ->addArgument(IUserService::class)
            ->addArgument(IRoleService::class)
            ->addArgument(IPermissionService::class);
    }

    private static function registerAuthModule(Container $container): void
    {
        // Modelos base para autenticación
        $container->addShared(User::class);
        $container->addShared(Role::class);
        $container->addShared(Permission::class);

        // Repositorios de autenticación
        $container->add(IUserRepository::class, UserRepository::class)
            ->addArgument(User::class);
        $container->add(IRoleRepository::class, RoleRepository::class)
            ->addArgument(Role::class);
        $container->add(IPermissionRepository::class, PermissionRepository::class)
            ->addArgument(Permission::class);

        // Servicios de autenticación
        $container->add(IRoleService::class, RoleService::class)
            ->addArgument(IRoleRepository::class);
        $container->add(IPermissionService::class, PermissionService::class)
            ->addArgument(IPermissionRepository::class);
        $container->add(IUserService::class, UserService::class)
            ->addArgument(IUserRepository::class)
            ->addArgument(IRoleRepository::class)
            ->addArgument(IPermissionRepository::class);
    }

    private static function registerAccommodationModule(Container $container): void
    {
        // Modelo de alojamiento
        $container->addShared(Accommodation::class);

        // Repositorio de alojamiento
        $container->add(IAccommodationRepository::class, AccommodationRepository::class)
            ->addArgument(Accommodation::class);

        // Servicio de alojamiento
        $container->add(IAccommodationService::class, AccommodationService::class)
            ->addArgument(IAccommodationRepository::class)
            ->addArgument(IUserService::class);
    }

    private static function registerReservationModule(Container $container): void
    {
        // Modelo de reserva
        $container->addShared(Reservation::class);

        // Repositorio de reserva
        $container->add(IReservationRepository::class, ReservationRepository::class)
            ->addArgument(Reservation::class);

        // Servicio de reserva
        $container->add(IReservationService::class, ReservationService::class)
            ->addArgument(IReservationRepository::class)
            ->addArgument(IAccommodationRepository::class)
            ->addArgument(ILoggerService::class);
    }

    private static function registerBaseServices(Container $container): void
    {
        // Registrar servicio de logging
        $container->addShared(ILoggerService::class, LoggerService::class);
    }
}
