<?php

namespace App\Utilities;

use League\Container\Container;
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
use App\Services\IUserService;
use App\Services\IRoleService;
use App\Services\IPermissionService;
use App\Services\IAccommodationService;

class ContainerBuilder
{
    private static ?Container $instance = null;

    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = self::buildContainer();
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

        return $container;
    }

    private static function registerBaseUtilities(Container $container): void
    {
        // Registrar validador de peticiones
        $container->addShared(IRequestValidator::class, RequestValidator::class);

        // Registrar autenticador
        $container->addShared(IAuthenticator::class, SessionAuthenticator::class)
            ->addArgument(IUserService::class);
    }

    private static function registerAuthModule(Container $container): void
    {
        // Modelos base para autenticación
        $container->addShared(User::class);
        $container->addShared(Role::class);
        $container->addShared(Permission::class);

        // Repositorios de autenticación
        $container->add(UserRepository::class)
            ->addArgument(User::class);
        $container->add(RoleRepository::class)
            ->addArgument(Role::class);
        $container->add(PermissionRepository::class)
            ->addArgument(Permission::class);

        // Servicios de autenticación
        $container->add(IRoleService::class, RoleService::class)
            ->addArgument(RoleRepository::class);
        $container->add(IPermissionService::class, PermissionService::class)
            ->addArgument(PermissionRepository::class);
        $container->add(IUserService::class, UserService::class)
            ->addArgument(UserRepository::class)
            ->addArgument(RoleRepository::class);
    }

    private static function registerAccommodationModule(Container $container): void
    {
        // Modelo de alojamiento
        $container->addShared(Accommodation::class);

        // Repositorio de alojamiento
        $container->add(AccommodationRepository::class)
            ->addArgument(Accommodation::class);

        // Servicio de alojamiento
        $container->add(IAccommodationService::class, AccommodationService::class)
            ->addArgument(AccommodationRepository::class)
            ->addArgument(IUserService::class);
    }
}
