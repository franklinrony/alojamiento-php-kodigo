<?php

use Phinx\Seed\AbstractSeed;

class PermissionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'access-admin',
                'description' => 'Acceder al panel de administración'
            ],
            [
                'name' => 'manage-accommodations',
                'description' => 'Gestionar alojamientos'
            ],
            [
                'name' => 'manage-users',
                'description' => 'Gestionar usuarios'
            ]
        ];

        $this->table('permissions')->insert($permissions)->save();

        // Crear rol de administrador
        $adminRole = [
            'name' => 'admin',
            'description' => 'Administrador del sistema'
        ];

        $this->table('roles')->insert($adminRole)->save();

        // Obtener el ID del rol admin
        $adminRoleId = $this->getAdapter()->getConnection()->lastInsertId();

        // Asignar todos los permisos al rol admin
        $permissionRoles = [];
        foreach ($permissions as $permission) {
            $permissionId = $this->fetchRow("SELECT id FROM permissions WHERE name = '{$permission['name']}'")['id'];
            $permissionRoles[] = [
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId
            ];
        }

        $this->table('permission_role')->insert($permissionRoles)->save();

        // Asignar rol admin al primer usuario (si existe)
        $firstUser = $this->fetchRow('SELECT id FROM users ORDER BY id LIMIT 1');
        if ($firstUser) {
            $this->execute("UPDATE users SET role_id = {$adminRoleId} WHERE id = {$firstUser['id']}");
        }
    }
}
