<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {
    // Rutas de autenticación
    $r->post('/api/auth/register', ['App\Controllers\Api\AuthController', 'register']);
    $r->post('/api/auth/login', ['App\Controllers\Api\AuthController', 'login']);
    $r->post('/api/auth/logout', ['App\Controllers\Api\AuthController', 'logout', 'middleware' => ['auth']]);

    // Rutas de usuario
    $r->get('/api/user/profile', ['App\Controllers\UserController', 'profile', 'middleware' => ['auth']]);
    $r->put('/api/user/profile', ['App\Controllers\UserController', 'updateProfile', 'middleware' => ['auth']]);
    $r->post('/api/user/role', [
        'App\Controllers\UserController', 
        'assignRole', 
        'middleware' => ['auth'],
        'permission' => 'assign_roles'
    ]);

    // Rutas de alojamientos
    $r->get('/api/accommodations', ['App\Controllers\AccommodationController', 'index']);
    $r->post('/api/accommodations', [
        'App\Controllers\AccommodationController', 
        'create', 
        'middleware' => ['auth'],
        'permission' => 'create_accommodation'
    ]);
    $r->get('/api/accommodations/{id:\d+}', ['App\Controllers\AccommodationController', 'show']);
    $r->put('/api/accommodations/{id:\d+}', [
        'App\Controllers\AccommodationController', 
        'update', 
        'middleware' => ['auth'],
        'permission' => 'update_accommodation'
    ]);
    $r->delete('/api/accommodations/{id:\d+}', [
        'App\Controllers\AccommodationController', 
        'delete', 
        'middleware' => ['auth'],
        'permission' => 'delete_accommodation'
    ]);

    // Rutas de reservas
    $r->get('/api/reservations/availability/{id:\d+}', ['App\Controllers\UserController', 'checkAvailability', 'middleware' => ['auth']]);
};
