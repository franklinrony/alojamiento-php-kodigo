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

    // Rutas de administración
    $r->get('/admin', ['App\Controllers\Admin\DashboardController', 'index', 'middleware' => ['auth', 'permission:access-admin']]);
    
    // Rutas de administración de alojamientos
    $r->get('/admin/accommodations', ['App\Controllers\Admin\AdminAccommodationController', 'index', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->get('/admin/accommodation/add', ['App\Controllers\Admin\AdminAccommodationController', 'showAddForm', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    $r->post('/admin/accommodation/add', ['App\Controllers\Admin\AdminAccommodationController', 'add', 'middleware' => ['auth', 'permission:manage-accommodations']]);
    
    // Rutas alternativas para compatibilidad
    $r->get('/accommodations/create', ['App\Controllers\RedirectController', 'redirectToAddAccommodation']);

    // Rutas de error
    $r->get('/error/403', ['App\Controllers\RedirectController', 'accessDenied']);
};
