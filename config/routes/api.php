<?php

use FastRoute\RouteCollector;

return function (RouteCollector $r) {
    // Rutas de autenticación
    $r->post('/api/auth/register', ['App\Controllers\AuthController', 'register']);
    $r->post('/api/auth/login', ['App\Controllers\AuthController', 'login']);
    $r->post('/api/auth/logout', ['App\Controllers\AuthController', 'logout']);

    // Rutas de usuario
    $r->get('/api/user/profile', ['App\Controllers\UserController', 'profile']);
    $r->put('/api/user/profile', ['App\Controllers\UserController', 'updateProfile']);
    $r->post('/api/user/role', ['App\Controllers\UserController', 'assignRole']);

    // Rutas de alojamientos
    $r->get('/api/accommodations', ['App\Controllers\AccommodationController', 'index']);
    $r->post('/api/accommodations', ['App\Controllers\AccommodationController', 'create']);
    $r->get('/api/accommodations/{id:\d+}', ['App\Controllers\AccommodationController', 'show']);
    $r->put('/api/accommodations/{id:\d+}', ['App\Controllers\AccommodationController', 'update']);
    $r->delete('/api/accommodations/{id:\d+}', ['App\Controllers\AccommodationController', 'delete']);
};
