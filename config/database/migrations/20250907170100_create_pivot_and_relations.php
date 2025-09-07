<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePivotAndRelations extends AbstractMigration
{
    public function change(): void
    {
        // Tabla pivote roles-permisos
        $this->table('role_permissions')
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addColumn('permission_id', 'integer', ['signed' => false])
            ->addForeignKey('role_id', 'roles', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('permission_id', 'permissions', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();

        // Agregar clave foránea a users.role_id
        $this->table('users')
            ->changeColumn('role_id', 'integer', ['null' => true, 'signed' => false])
            ->addForeignKey('role_id', 'roles', 'id', ['delete'=> 'SET_NULL', 'update'=> 'NO_ACTION'])
            ->update();

        // Agregar clave foránea a accommodations.created_by
        $this->table('accommodations')
            ->changeColumn('created_by', 'integer', ['null' => true, 'signed' => false])
            ->addForeignKey('created_by', 'users', 'id', ['delete'=> 'SET_NULL', 'update'=> 'NO_ACTION'])
            ->update();

        // Tabla pivote usuario-alojamiento
        $this->table('user_accommodations')
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('accommodation_id', 'integer', ['signed' => false])
            ->addTimestamps()
            ->addForeignKey('user_id', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('accommodation_id', 'accommodations', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
