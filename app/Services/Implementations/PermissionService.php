<?php

namespace App\Services\Implementations;

use App\Models\Permission;
use App\Repositories\IPermissionRepository;
use App\Services\IPermissionService;

/**
 * Class PermissionService
 * Implementación del servicio de permisos
 */
class PermissionService implements IPermissionService
{
    /**
     * @var IPermissionRepository
     */
    private $permissionRepository;

    /**
     * @param IPermissionRepository $permissionRepository
     */
    public function __construct(IPermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @inheritDoc
     */
    public function createPermission(array $permissionData): Permission
    {
        if ($this->permissionExists($permissionData['name'])) {
            throw new \RuntimeException('Ya existe un permiso con ese nombre');
        }

        return $this->permissionRepository->create($permissionData);
    }

    /**
     * @inheritDoc
     */
    public function updatePermission(int $permissionId, array $permissionData): ?Permission
    {
        $permission = $this->permissionRepository->find($permissionId);
        if (!$permission) {
            return null;
        }

        // Verificar si el nuevo nombre ya existe (si se está actualizando el nombre)
        if (isset($permissionData['name']) && $permissionData['name'] !== $permission->getName()) {
            if ($this->permissionExists($permissionData['name'])) {
                throw new \RuntimeException('Ya existe un permiso con ese nombre');
            }
        }

        return $this->permissionRepository->update($permissionId, $permissionData);
    }

    /**
     * @inheritDoc
     */
    public function permissionExists(string $name): bool
    {
        return $this->permissionRepository->findByName($name) !== null;
    }

    /**
     * @inheritDoc
     */
    public function getRolesWithPermission(int $permissionId): array
    {
        $permission = $this->permissionRepository->find($permissionId);
        if (!$permission) {
            return [];
        }

        return $this->permissionRepository->getRoles($permissionId);
    }

    /**
     * @inheritDoc
     */
    public function getAllPermissions(): array
    {
        return $this->permissionRepository->all();
    }
}
