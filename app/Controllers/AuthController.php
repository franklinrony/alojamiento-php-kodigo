<?php

namespace App\Controllers;

use App\Services\IUserService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

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
     * @var IRequestValidator|null
     */
    protected ?IRequestValidator $validator;

    /**
     * @var IAuthenticator|null
     */
    protected ?IAuthenticator $authenticator;

    /**
     * @param IUserService $userService
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     */
    public function __construct(
        IUserService $userService,
        IRequestValidator $validator,
        IAuthenticator $authenticator
    ) {
        $this->userService = $userService;
        $this->validator = $validator;
        $this->authenticator = $authenticator;
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

        try {
            $rules = [
                'email' => ['required' => true, 'type' => 'string', 'email' => true],
                'password' => ['required' => true, 'type' => 'string', 'min' => 6],
                'name' => ['required' => true, 'type' => 'string', 'min' => 2]
            ];

            $data = $this->getJsonRequest($rules);

            $user = $this->userService->register($data);
            
            // Autenticar al usuario después del registro si está activo
            if ($user->isActive()) {
                $this->authenticator->authenticate($data['email'], $data['password']);
            }
            
            // Preparar respuesta sin datos sensibles
            $userData = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName()
            ];
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'user' => $userData
            ], 201);

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
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
        
        // Reglas de validación para login
        $rules = [
            'email' => ['required' => true, 'type' => 'string', 'email' => true],
            'password' => ['required' => true, 'type' => 'string', 'min' => 6]
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->error('Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            return;
        }

        try {
            if (!$this->authenticator->authenticate($data['email'], $data['password'])) {
                $this->error('Credenciales inválidas', 401);
                return;
            }

            $userData = $this->authenticator->getAuthenticatedUser();
            $this->success($userData, 'Inicio de sesión exitoso');
            
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 401);
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

        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No hay sesión activa', 400);
            return;
        }

        $this->authenticator->logout();
        $this->jsonResponse([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
}
