<?php

namespace App\Controllers\Api;

use App\Services\IUserService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class AuthController
 * Controlador de autenticación para la API
 */
class AuthController extends BaseApiController
{
    /**
     * @var IUserService
     */
    private $userService;

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
        parent::__construct($validator, $authenticator);
        $this->userService = $userService;
    }

    /**
     * Maneja el inicio de sesión vía API
     */
    public function login(): void
    {
        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        try {
            $rules = [
                'email' => ['required' => true, 'type' => 'string', 'email' => true],
                'password' => ['required' => true, 'type' => 'string', 'min' => 6]
            ];

            $data = $this->getJsonRequest($rules);

            if (!$this->authenticator->authenticate($data['email'], $data['password'])) {
                $this->error('Credenciales inválidas', 401);
                return;
            }

            $userData = $this->authenticator->getAuthenticatedUser();
            $this->success($userData, 'Inicio de sesión exitoso');

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 401);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Maneja el registro de usuarios vía API
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
            
            $this->success($userData, 'Usuario registrado exitosamente', 201);

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Maneja el cierre de sesión vía API
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
        $this->success(null, 'Sesión cerrada exitosamente');
    }
}
