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
        
        // Log de password usando el sistema de logging
        if (class_exists('\App\Services\ILoggerService')) {
            try {
                $container = \App\Utilities\DiContainer::getInstance();
                if ($container->has(\App\Services\ILoggerService::class)) {
                    $logger = $container->get(\App\Services\ILoggerService::class);
                    $logger->debug("Password establecido para usuario", [
                        'user_id' => $this->id,
                        'email' => $this->email,
                        'password_hash_preview' => substr($this->password, 0, 10) . '...'
                    ]);
                }
            } catch (\Exception $logError) {
                // Fallback silencioso
            }
        }
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
            // Log de error usando el sistema de logging
            if (class_exists('\App\Services\ILoggerService')) {
                try {
                    $container = \App\Utilities\DiContainer::getInstance();
                    if ($container->has(\App\Services\ILoggerService::class)) {
                        $logger = $container->get(\App\Services\ILoggerService::class);
                        $logger->error("Hash de contraseña vacío para usuario", [
                            'user_id' => $this->id,
                            'email' => $this->email
                        ]);
                    }
                } catch (\Exception $logError) {
                    // Fallback silencioso
                }
            }
            return false;
        }

        $result = password_verify($password, $this->password);
        if (!$result) {
            // Log de verificación fallida usando el sistema de logging
            if (class_exists('\App\Services\ILoggerService')) {
                try {
                    $container = \App\Utilities\DiContainer::getInstance();
                    if ($container->has(\App\Services\ILoggerService::class)) {
                        $logger = $container->get(\App\Services\ILoggerService::class);
                        $logger->warning("Verificación de contraseña fallida", [
                            'user_id' => $this->id,
                            'email' => $this->email,
                            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                        ]);
                    }
                } catch (\Exception $logError) {
                    // Fallback silencioso
                }
            }
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
