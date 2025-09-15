<?php

namespace App\Models;

/**
 * Class Role
 * Modelo para la tabla roles
 */
class Role extends Model
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var Permission[]
     */
    protected $permissions = [];

    /**
     * @var User[]
     */
    protected $users = [];

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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param Permission[] $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * Agrega un permiso al rol
     *
     * @param Permission $permission
     */
    public function addPermission(Permission $permission): void
    {
        $this->permissions[] = $permission;
    }

    /**
     * Verifica si el rol tiene un permiso específico
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getName() === $permissionName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param User[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    /**
     * Agrega un usuario al rol
     *
     * @param User $user
     */
    public function addUser(User $user): void
    {
        $this->users[] = $user;
    }
}
