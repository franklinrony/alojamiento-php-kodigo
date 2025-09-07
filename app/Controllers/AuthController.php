<?php

namespace App\Controllers;

use App\Services\IUserService;

/**
 * Class AuthController
 * Controlador para manejo de autenticación
 */
class AuthController extends BaseController
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @param IUserService $userService
     */
    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Maneja el registro de usuarios
     */
    public function register(): void
    {
        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        $data = $this->getJsonRequest();
        
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
            $this->error('Datos incompletos');
            return;
        }

        try {
            $user = $this->userService->register($data);
            
            // No devolver la contraseña en la respuesta
            unset($user->password);
            
            $this->jsonResponse([
                'message' => 'Usuario registrado exitosamente',
                'user' => $user
            ], 201);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Maneja el inicio de sesión
     */
    public function login(): void
    {
        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        $data = $this->getJsonRequest();
        
        if (!isset($data['email']) || !isset($data['password'])) {
            $this->error('Datos incompletos');
            return;
        }

        try {
            $user = $this->userService->login($data['email'], $data['password']);
            
            if (!$user) {
                $this->error('Credenciales inválidas', 401);
                return;
            }

            // Iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_name'] = $user->getName();
            
            // No devolver la contraseña en la respuesta
            unset($user->password);
            
            $this->jsonResponse([
                'message' => 'Inicio de sesión exitoso',
                'user' => $user
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout(): void
    {
        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        session_start();
        session_destroy();
        
        $this->jsonResponse([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
}
