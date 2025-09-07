<?php

namespace App\Repositories\Implementations;

use App\Models\User;
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

        return $this->mapToModel($result);
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
}
