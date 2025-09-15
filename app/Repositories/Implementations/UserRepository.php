<?php

namespace App\Repositories\Implementations;

use App\Models\User;
use App\Models\Role;
use App\Repositories\IUserRepository;

/**
 * Class UserRepository
 * Implementación del repositorio de usuarios
 */
class UserRepository extends BaseRepository implements IUserRepository
{
    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        $user = $this->mapToModel($result);
        $this->loadUserRole($user);
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function findByRole(int $roleId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);
        
        $results = $stmt->fetchAll();
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * @inheritDoc
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        $user = parent::find($id);
        if ($user) {
            $this->loadUserRole($user);
        }
        return $user;
    }

    /**
     * Carga la información del rol del usuario
     *
     * @param User $user
     * @return void
     */
    private function loadUserRole(User $user): void
    {
        if (!$user->getRoleId()) {
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :role_id");
        $stmt->execute(['role_id' => $user->getRoleId()]);
        
        $roleData = $stmt->fetch();
        if ($roleData) {
            $role = new Role();
            $role->setId($roleData['id']);
            $role->setName($roleData['name']);
            $role->setDescription($roleData['description'] ?? null);
            $user->setRole($role);
        }
    }
}
