<?php

namespace App\Services\Implementations;

use App\Models\Role;
use App\Repositories\IRoleRepository;
use App\Services\IRoleService;

/**
 * Class RoleService
 * Implementación del servicio de roles
 */
class RoleService implements IRoleService
{
    /**
     * @var IRoleRepository
     */
    private $roleRepository;

    /**
     * @param IRoleRepository $roleRepository
     */
    public function __construct(IRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @inheritDoc
     */
    public function createRole(array $roleData): Role
    {
        if ($this->roleExists($roleData['name'])) {
            throw new \RuntimeException('Ya existe un rol con ese nombre');
        }

        return $this->roleRepository->create($roleData);
    }

    /**
     * @inheritDoc
     */
    public function updateRole(int $roleId, array $roleData): ?Role
    {
        $role = $this->roleRepository->find($roleId);
        if (!$role) {
            return null;
        }

        // Verificar si el nuevo nombre ya existe (si se está actualizando el nombre)
        if (isset($roleData['name']) && $roleData['name'] !== $role->getName()) {
            if ($this->roleExists($roleData['name'])) {
                throw new \RuntimeException('Ya existe un rol con ese nombre');
            }
        }

        return $this->roleRepository->update($roleId, $roleData);
    }

    /**
     * @inheritDoc
     */
    public function assignPermissions(int $roleId, array $permissionIds): bool
    {
        $role = $this->roleRepository->find($roleId);
        if (!$role) {
            return false;
        }

        return $this->roleRepository->assignPermissions($roleId, $permissionIds);
    }

    /**
     * @inheritDoc
     */
    public function roleExists(string $name): bool
    {
        return $this->roleRepository->findByName($name) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getRoleWithPermissions(int $roleId): ?Role
    {
        $role = $this->roleRepository->find($roleId);
        if (!$role) {
            return null;
        }

        // Cargar los permisos
        $permissions = $this->roleRepository->getPermissions($roleId);
        $role->setPermissions($permissions);

        return $role;
    }
}
