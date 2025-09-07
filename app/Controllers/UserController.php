<?php

namespace App\Controllers;

use App\Services\IUserService;
use App\Services\IRoleService;

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
     * @param IUserService $userService
     * @param IRoleService $roleService
     */
    public function __construct(IUserService $userService, IRoleService $roleService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    /**
     * Obtiene el perfil del usuario autenticado
     */
    public function profile(): void
    {
        if (!$this->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        $userId = $this->getAuthUserId();
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
        if (!$this->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('PUT')) {
            $this->error('Método no permitido', 405);
            return;
        }

        $userId = $this->getAuthUserId();
        $data = $this->getJsonRequest();

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
        if (!$this->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        // Verificar permiso de administración
        if (!$this->userService->hasPermission($this->getAuthUserId(), 'manage_users')) {
            $this->error('No tiene permisos para realizar esta acción', 403);
            return;
        }

        $data = $this->getJsonRequest();
        
        if (!isset($data['user_id']) || !isset($data['role_id'])) {
            $this->error('Datos incompletos');
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
