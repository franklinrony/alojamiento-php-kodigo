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
        // Permisos básicos
        $permissions = [
            ['name' => 'manage_users', 'description' => 'Gestionar usuarios'],
            ['name' => 'manage_accommodations', 'description' => 'Gestionar alojamientos'],
            ['name' => 'view_accommodations', 'description' => 'Ver alojamientos'],
        ];
        $this->table('permissions')->insert($permissions)->saveData();

        // Roles
        $roles = [
            ['name' => 'admin', 'description' => 'Administrador'],
            ['name' => 'user', 'description' => 'Usuario'],
        ];
        $this->table('roles')->insert($roles)->saveData();

        // Relacionar permisos con roles (admin: todos, user: solo ver)
        $rolePermissions = [
            // admin: todos los permisos
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
            // user: solo ver alojamientos
            ['role_id' => 2, 'permission_id' => 3],
        ];
        $this->table('role_permissions')->insert($rolePermissions)->saveData();

        // Usuarios por defecto
        $password = password_hash('qwerty', PASSWORD_DEFAULT);
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@demo.com',
                'password' => $password,
                'role_id' => 1,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Usuario1',
                'email' => 'user1@demo.com',
                'password' => $password,
                'role_id' => 2,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Usuario2',
                'email' => 'user2@demo.com',
                'password' => $password,
                'role_id' => 2,
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->table('users')->insert($users)->saveData();
    }
}
