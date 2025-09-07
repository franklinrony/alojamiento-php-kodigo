<?php

namespace App\Repositories\Implementations;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\IRoleRepository;
use PDOException;

/**
 * Class RoleRepository
 * Implementación del repositorio de roles
 */
class RoleRepository extends BaseRepository implements IRoleRepository
{
    /**
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $name)
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE name = :name");
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
    public function getPermissions(int $roleId)
    {
        $sql = "SELECT p.* FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        return array_map(function($data) {
            $permission = new Permission();
            foreach ($data as $key => $value) {
                $setter = $this->getSetterMethod($key);
                if (method_exists($permission, $setter)) {
                    $permission->$setter($value);
                }
            }
            return $permission;
        }, $stmt->fetchAll());
    }

    /**
     * @inheritDoc
     */
    public function assignPermissions(int $roleId, array $permissionIds)
    {
        try {
            $this->db->beginTransaction();

            // Eliminar permisos existentes
            $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
            $stmt->execute(['role_id' => $roleId]);

            // Insertar nuevos permisos
            $stmt = $this->db->prepare(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)"
            );

            foreach ($permissionIds as $permissionId) {
                $stmt->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            // TODO: Implement proper error logging
            return false;
        }
    }
}
