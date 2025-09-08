<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Limpiar datos existentes primero
        $this->execute('DELETE FROM permission_role');
        $this->execute('DELETE FROM users');
        $this->execute('DELETE FROM accommodations');
        $this->execute('DELETE FROM permissions');
        $this->execute('DELETE FROM roles');

        // Permisos básicos
        $permissions = [
            ['name' => 'access-admin', 'description' => 'Acceder al panel de administración'],
            ['name' => 'manage-users', 'description' => 'Gestionar usuarios'],
            ['name' => 'manage-accommodations', 'description' => 'Gestionar alojamientos'],
            ['name' => 'view-accommodations', 'description' => 'Ver alojamientos'],
        ];
        $this->table('permissions')->insert($permissions)->saveData();

        // Obtener IDs de permisos insertados
        $permissionIds = [];
        foreach ($permissions as $permission) {
            $result = $this->fetchRow("SELECT id FROM permissions WHERE name = '{$permission['name']}'");
            $permissionIds[$permission['name']] = $result['id'];
        }

        // Roles
        $roles = [
            ['name' => 'admin', 'description' => 'Administrador'],
            ['name' => 'user', 'description' => 'Usuario'],
        ];
        $this->table('roles')->insert($roles)->saveData();

        // Obtener IDs de roles insertados
        $roleIds = [];
        foreach ($roles as $role) {
            $result = $this->fetchRow("SELECT id FROM roles WHERE name = '{$role['name']}'");
            $roleIds[$role['name']] = $result['id'];
        }

        // Relacionar permisos con roles usando IDs reales
        $rolePermissions = [
            // admin: todos los permisos
            ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['access-admin']],
            ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['manage-users']],
            ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['manage-accommodations']],
            ['role_id' => $roleIds['admin'], 'permission_id' => $permissionIds['view-accommodations']],
            // user: solo ver alojamientos
            ['role_id' => $roleIds['user'], 'permission_id' => $permissionIds['view-accommodations']],
        ];
        $this->table('permission_role')->insert($rolePermissions)->saveData();

        // Usuarios por defecto
        $password = password_hash('qwerty', PASSWORD_DEFAULT);
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@demo.com',
                'password' => $password,
                'role_id' => $roleIds['admin'],
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Usuario Demo',
                'email' => 'user@demo.com',
                'password' => $password,
                'role_id' => $roleIds['user'],
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->table('users')->insert($users)->saveData();
    }
}
