<?php

use Phinx\Seed\AbstractSeed;

class PermissionSeeder extends AbstractSeed
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Limpiar las tablas existentes
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->table('permissions')->truncate();
        $this->table('permission_role')->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');

        // Definir permisos básicos
        $permissions = [
            ['name' => 'access-admin', 'description' => 'Acceso al panel de administración'],
            ['name' => 'manage-accommodations', 'description' => 'Gestionar alojamientos'],
            ['name' => 'manage_users', 'description' => 'Gestionar usuarios'],
            ['name' => 'manage_roles', 'description' => 'Gestionar roles y permisos'],
            ['name' => 'create_accommodation', 'description' => 'Crear alojamientos'],
            ['name' => 'edit_accommodation', 'description' => 'Editar alojamientos'],
            ['name' => 'delete_accommodation', 'description' => 'Eliminar alojamientos'],
            ['name' => 'manage_all_accommodations', 'description' => 'Gestionar todos los alojamientos'],
            ['name' => 'view_users', 'description' => 'Ver usuarios'],
            ['name' => 'edit_users', 'description' => 'Editar usuarios'],
            ['name' => 'delete_users', 'description' => 'Eliminar usuarios'],
        ];

        // Insertar permisos
        $this->table('permissions')
            ->insert($permissions)
            ->saveData();

        // Obtener el ID del rol de administrador
        $adminRole = $this->fetchRow("SELECT id FROM roles WHERE name = 'admin'");
        
        if ($adminRole) {
            // Obtener todos los permisos insertados
            $insertedPermissions = $this->fetchAll("SELECT id FROM permissions");
            
            // Asignar todos los permisos al rol de administrador
            $rolePermissions = array_map(function($permission) use ($adminRole) {
                return [
                    'role_id' => $adminRole['id'],
                    'permission_id' => $permission['id']
                ];
            }, $insertedPermissions);

            // Insertar las relaciones rol-permiso
            $this->table('permission_role')
                ->insert($rolePermissions)
                ->saveData();
        }
    }
}
