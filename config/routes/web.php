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
};
