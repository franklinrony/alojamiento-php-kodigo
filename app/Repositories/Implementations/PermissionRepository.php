<?php

namespace App\Repositories\Implementations;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\IPermissionRepository;

/**
 * Class PermissionRepository
 * Implementación del repositorio de permisos
 */
class PermissionRepository extends BaseRepository implements IPermissionRepository
{
    /**
     * @param Permission $model
     */
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name)
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE name = :name");
        $stmt->execute(['name' => $name]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        return $this->mapToModel($result);
    }

    /**
     * @inheritDoc
     */
    public function getRoles(int $permissionId)
    {
        $sql = "SELECT r.* FROM roles r
                INNER JOIN permission_role pr ON r.id = pr.role_id
                WHERE pr.permission_id = :permission_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['permission_id' => $permissionId]);
        
        return array_map(function($data) {
            $role = new Role();
            foreach ($data as $key => $value) {
                $setter = $this->getSetterMethod($key);
                if (method_exists($role, $setter)) {
                    $role->$setter($value);
                }
            }
            return $role;
        }, $stmt->fetchAll());
    }

    /**
     * @inheritDoc
     */
    public function getPermissionsByRoleId(int $roleId): array
    {
        $sql = "SELECT p.* FROM permissions p
                INNER JOIN permission_role pr ON p.id = pr.permission_id
                WHERE pr.role_id = :role_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        return array_map(fn($data) => $this->mapToModel($data), $stmt->fetchAll());
    }
}
