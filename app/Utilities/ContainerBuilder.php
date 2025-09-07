<?php

namespace App\Utilities;

use League\Container\Container;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Accommodation;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AccommodationController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\CorsMiddleware;
use App\Middlewares\PermissionMiddleware;
use App\Utilities\PathHelper;
use App\Repositories\Implementations\UserRepository;
use App\Repositories\Implementations\RoleRepository;
use App\Repositories\Implementations\PermissionRepository;
use App\Repositories\Implementations\AccommodationRepository;
use App\Services\Implementations\UserService;
use App\Services\Implementations\RoleService;
use App\Services\Implementations\PermissionService;
use App\Services\Implementations\AccommodationService;
use App\Services\IUserService;
use App\Services\IRoleService;
use App\Services\IPermissionService;
use App\Services\IAccommodationService;
use App\Repositories\IUserRepository;
use App\Repositories\IRoleRepository;
use App\Repositories\IPermissionRepository;
use App\Repositories\IAccommodationRepository;

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

        // Registrar utilidades base
        self::registerBaseUtilities($container);

        // Registrar módulos según se necesiten
        self::registerAuthModule($container);
        self::registerAccommodationModule($container);
        
        // Registrar controladores y middlewares
        self::registerControllers($container);
        self::registerMiddlewares($container);

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
        $container->addShared(\Twig\Environment::class, function () {
            $loader = new \Twig\Loader\FilesystemLoader(PathHelper::getViewsPath());
            $twig = new \Twig\Environment($loader, [
                'cache' => PathHelper::getCachePath() . '/twig',
                'debug' => $_ENV['APP_DEBUG'] === 'true',
                'auto_reload' => $_ENV['APP_DEBUG'] === 'true'
            ]);
            
            // Si estamos en modo debug, agregar la extensión de debug de Twig
            if ($_ENV['APP_DEBUG'] === 'true') {
                $twig->addExtension(new \Twig\Extension\DebugExtension());
            }
            
            return $twig;
        });
    }

    private static function registerControllers(Container $container): void
    {
        // Registrar HomeController
        $container->add(HomeController::class)
            ->addArguments([
                \Twig\Environment::class,
                IRequestValidator::class,
                IAuthenticator::class
            ]);

        // Registrar UserController
        $container->add(\App\Controllers\UserController::class)
            ->addArguments([
                \Twig\Environment::class,
                IRequestValidator::class,
                IAuthenticator::class,
                IUserService::class,
                IRoleService::class
            ]);

        // Registrar AuthController
        $container->add(AuthController::class)
            ->addArgument(IUserService::class)
            ->addArgument(\Twig\Environment::class)
            ->addArgument(IRequestValidator::class)
            ->addArgument(IAuthenticator::class);

        // Registrar AccommodationController
        $container->add(AccommodationController::class)
            ->addArgument(IAccommodationService::class)
            ->addArgument(\Twig\Environment::class)
            ->addArgument(IRequestValidator::class)
            ->addArgument(IAuthenticator::class);
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
            ->addArgument(IRoleRepository::class);
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
}
