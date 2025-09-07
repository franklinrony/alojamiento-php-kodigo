<?php

namespace App\Controllers;

use App\Services\IUserService;
use App\Services\IRoleService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class UserController
 * Controlador para gestión de usuarios
 */
class UserController extends BaseController
{
    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var IRoleService
     */
    private $roleService;

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
     * @param IRoleService $roleService
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     */
    public function __construct(
        IUserService $userService, 
        IRoleService $roleService,
        IRequestValidator $validator,
        IAuthenticator $authenticator
    ) {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->validator = $validator;
        $this->authenticator = $authenticator;
    }

    /**
     * Obtiene el perfil del usuario autenticado
     */
    public function profile(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        $userId = $this->authenticator->getUserId();
        $user = $this->userService->getUser($userId);

        if (!$user) {
            $this->error('Usuario no encontrado', 404);
            return;
        }

        // No devolver la contraseña en la respuesta
        unset($user->password);
        
        $this->jsonResponse(['user' => $user]);
    }

    /**
     * Actualiza el perfil del usuario autenticado
     */
    public function updateProfile(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('PUT')) {
            $this->error('Método no permitido', 405);
            return;
        }

        $data = $this->getJsonRequest();
        
        // Reglas de validación para actualización de perfil
        $rules = [
            'name' => ['type' => 'string', 'min' => 2],
            'email' => ['type' => 'string', 'email' => true],
            'current_password' => ['type' => 'string', 'min' => 6],
            'new_password' => ['type' => 'string', 'min' => 6]
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->error('Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            return;
        }

        $data = $this->validator->sanitize($data);
        $userId = $this->authenticator->getUserId();

        try {
            $user = $this->userService->updateUser($userId, $data);
            
            if (!$user) {
                $this->error('Error al actualizar el perfil', 400);
                return;
            }

            // No devolver la contraseña en la respuesta
            unset($user->password);
            
            $this->jsonResponse([
                'message' => 'Perfil actualizado exitosamente',
                'user' => $user
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Asigna un rol a un usuario (solo admin)
     */
    public function assignRole(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        // Verificar permiso de administración
        if (!$this->userService->hasPermission($this->authenticator->getUserId(), 'manage_users')) {
            $this->error('No tiene permisos para realizar esta acción', 403);
            return;
        }

        $data = $this->getJsonRequest();
        
        // Reglas de validación para asignación de rol
        $rules = [
            'user_id' => ['required' => true, 'type' => 'integer'],
            'role_id' => ['required' => true, 'type' => 'integer']
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->error('Errores de validación: ' . implode(', ', $this->validator->getErrors()));
            return;
        }

        try {
            $success = $this->userService->assignRole($data['user_id'], $data['role_id']);
            
            if (!$success) {
                $this->error('Error al asignar el rol');
                return;
            }

            $this->jsonResponse([
                'message' => 'Rol asignado exitosamente'
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
        }
    }
}
