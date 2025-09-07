<?php

namespace App\Services\Implementations;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Repositories\IRoleRepository;
use App\Services\IUserService;

/**
 * Class UserService
 * Implementación del servicio de usuarios
 */
class UserService implements IUserService
{
    /**
     * @var IUserRepository
     */
    private $userRepository;

    /**
     * @var IRoleRepository
     */
    private $roleRepository;

    /**
     * @param IUserRepository $userRepository
     * @param IRoleRepository $roleRepository
     */
    public function __construct(IUserRepository $userRepository, IRoleRepository $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @inheritDoc
     */
    public function register(array $userData): User
    {
        if ($this->userRepository->emailExists($userData['email'])) {
            throw new \RuntimeException('El email ya está registrado');
        }

        // Filtrar solo los campos permitidos
        $filteredData = array_intersect_key($userData, array_flip([
            'email',
            'password',
            'name',
            'role_id'
        ]));

        // Asegurarse de que el usuario esté activo por defecto
        $filteredData['active'] = true;
        
        // Establecer rol por defecto si no se proporciona
        if (!isset($filteredData['role_id'])) {
            $filteredData['role_id'] = 2; // ID del rol 'user'
        }
        
        // Hash de la contraseña
        $filteredData['password'] = password_hash($filteredData['password'], PASSWORD_DEFAULT);

        // Agregar timestamps
        $now = date('Y-m-d H:i:s');
        $filteredData['created_at'] = $now;
        $filteredData['updated_at'] = $now;

        return $this->userRepository->create($filteredData);
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * @inheritDoc
     */
    public function login(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }

        if (!$user->isActive()) {
            throw new \RuntimeException('La cuenta está desactivada');
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function updateUser(int $userId, array $userData): ?User
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return null;
        }

        // Si se está actualizando el email, verificar que no exista
        if (isset($userData['email']) && $userData['email'] !== $user->getEmail()) {
            if ($this->userRepository->emailExists($userData['email'])) {
                throw new \RuntimeException('El email ya está en uso');
            }
        }

        return $this->userRepository->update($userId, $userData);
    }

    /**
     * @inheritDoc
     */
    public function assignRole(int $userId, int $roleId): bool
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return false;
        }

        $role = $this->roleRepository->find($roleId);
        if (!$role) {
            return false;
        }

        return (bool) $this->userRepository->update($userId, ['role_id' => $roleId]);
    }

    /**
     * @inheritDoc
     */
    public function hasPermission(int $userId, string $permissionName): bool
    {
        $user = $this->userRepository->find($userId);
        if (!$user || !$user->getRole()) {
            return false;
        }

        return $user->getRole()->hasPermission($permissionName);
    }

    /**
     * @inheritDoc
     */
    public function getUser(int $userId): ?User
    {
        return $this->userRepository->find($userId);
    }
}
