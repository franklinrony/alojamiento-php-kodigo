<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {
    // Ruta principal
    $r->get('/', ['App\Controllers\HomeController', 'index']);

    // Rutas de autenticación (vistas)
    $r->get('/auth/login', ['App\Controllers\AuthController', 'loginPage']);
    $r->get('/auth/register', ['App\Controllers\AuthController', 'registerPage']);
    
    // Rutas de autenticación (acciones)
    $r->post('/auth/login', ['App\Controllers\AuthController', 'login']);
    $r->post('/auth/register', ['App\Controllers\AuthController', 'register']);
    $r->post('/auth/logout', ['App\Controllers\AuthController', 'logout', 'middleware' => ['auth']]);

    // Rutas de usuario
    $r->get('/profile', ['App\Controllers\UserController', 'profile', 'middleware' => ['auth']]);
    $r->post('/profile', ['App\Controllers\UserController', 'updateProfile', 'middleware' => ['auth']]);

    // Rutas de alojamientos (usuarios)
    $r->get('/accommodations', ['App\Controllers\AccommodationController', 'index']);
    $r->get('/accommodations/{id:\d+}', ['App\Controllers\AccommodationController', 'show']);
    $r->get('/accommodations/create', ['App\Controllers\AccommodationController', 'create', 'middleware' => ['auth']]);
    $r->post('/accommodations/store', ['App\Controllers\AccommodationController', 'store', 'middleware' => ['auth']]);
    $r->get('/accommodations/{id:\d+}/edit', ['App\Controllers\AccommodationController', 'edit', 'middleware' => ['auth']]);
    $r->post('/accommodations/{id:\d+}/update', ['App\Controllers\AccommodationController', 'update', 'middleware' => ['auth']]);

    // Rutas de administración
    $r->get('/admin', ['App\Controllers\Admin\DashboardController', 'index', 'middleware' => ['auth', 'permission:access-admin']]);
    
    // Rutas de administración de alojamientos (nuevo controlador)
    $r->get('/admin/accommodations', ['App\Controllers\Admin\AccommodationController', 'index', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->get('/admin/accommodations/create', ['App\Controllers\Admin\AccommodationController', 'create', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->post('/admin/accommodations/store', ['App\Controllers\Admin\AccommodationController', 'store', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->get('/admin/accommodations/{id:\d+}/edit', ['App\Controllers\Admin\AccommodationController', 'edit', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->post('/admin/accommodations/{id:\d+}/update', ['App\Controllers\Admin\AccommodationController', 'update', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    
    // Rutas de administración de alojamientos (controlador anterior - mantener para compatibilidad)
    $r->get('/admin/accommodation/add', ['App\Controllers\Admin\AdminAccommodationController', 'showAddForm', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->post('/admin/accommodation/add', ['App\Controllers\Admin\AdminAccommodationController', 'add', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    
    // Rutas alternativas para compatibilidad (eliminada - duplicada con AccommodationController)

    // Rutas de error
    $r->get('/error/403', ['App\Controllers\RedirectController', 'accessDenied']);
};
