<?php

namespace App\Models;

/**
 * Class User
 * Modelo para la tabla users
 */
class User extends Model
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int|null
     */
    protected $role_id;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var Role|null
     */
    protected $role;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $password
     */
    /**
     * Establece el hash de la contraseña directamente
     * @param string $hash
     */
    private function setPasswordHash(string $hash): void
    {
        $this->password = $hash;
    }

    public function setPassword(string $password): void
    {
        // Si el password ya parece ser un hash, asignarlo directamente
        if (strlen($password) === 60 && strpos($password, '$2y$') === 0) {
            $this->setPasswordHash($password);
        } else {
            $this->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
        }
        
        error_log("Password establecido: " . substr($this->password, 0, 10) . "...");
    }

    /**
     * Verifica si la contraseña proporcionada es correcta
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        if (empty($this->password)) {
            error_log("Error: Hash de contraseña vacío para el usuario " . $this->email);
            return false;
        }

        $result = password_verify($password, $this->password);
        if (!$result) {
            error_log("Verificación de contraseña fallida para el usuario " . $this->email);
        }
        return $result;
    }

    /**
     * @return int|null
     */
    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    /**
     * @param int|null $roleId
     */
    public function setRoleId(?int $roleId): void
    {
        $this->role_id = $roleId;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role|null $role
     */
    public function setRole(?Role $role): void
    {
        $this->role = $role;
        $this->role_id = $role ? $role->getId() : null;
    }
}
