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
    $r->get('/api/accommodations', ['App\Controllers\Api\AccommodationApiController', 'index']);
    $r->post('/api/accommodations', [
        'App\Controllers\Api\AccommodationApiController', 
        'create', 
        'middleware' => ['auth'],
        'permission' => 'create_accommodation'
    ]);
    $r->get('/api/accommodations/{id:\d+}', ['App\Controllers\Api\AccommodationApiController', 'show']);
    $r->put('/api/accommodations/{id:\d+}', [
        'App\Controllers\Api\AccommodationApiController', 
        'update', 
        'middleware' => ['auth'],
        'permission' => 'update_accommodation'
    ]);
    $r->delete('/api/accommodations/{id:\d+}', [
        'App\Controllers\Api\AccommodationApiController', 
        'delete', 
        'middleware' => ['auth'],
        'permission' => 'delete_accommodation'
    ]);

    // Rutas de reservas
    $r->get('/api/reservations', ['App\Controllers\Api\ReservationApiController', 'index', 'middleware' => ['auth']]);
    $r->post('/api/reservations', ['App\Controllers\Api\ReservationApiController', 'create', 'middleware' => ['auth']]);
    $r->get('/api/reservations/{id:\d+}', ['App\Controllers\Api\ReservationApiController', 'show', 'middleware' => ['auth']]);
    $r->put('/api/reservations/{id:\d+}', ['App\Controllers\Api\ReservationApiController', 'update', 'middleware' => ['auth']]);
    $r->post('/api/reservations/{id:\d+}/cancel', ['App\Controllers\Api\ReservationApiController', 'cancel', 'middleware' => ['auth']]);
    $r->get('/api/reservations/availability/{id:\d+}', ['App\Controllers\Api\ReservationApiController', 'checkAvailability']);
    $r->get('/api/reservations/stats', ['App\Controllers\Api\ReservationApiController', 'stats', 'middleware' => ['auth']]);
};
