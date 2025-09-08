<?php

use Phinx\Db\Adapter\MysqlAdapter;

class FixTablesStructure extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        // Primero eliminamos las tablas existentes que necesitamos recrear
        if ($this->hasTable('permission_role')) {
            $this->table('permission_role')->drop()->save();
        }

        if ($this->hasTable('role_permissions')) {
            $this->table('role_permissions')->drop()->save();
        }

        // Recreamos la tabla permission_role con la estructura correcta
        $this->table('permission_role', [
            'id' => false,
            'primary_key' => ['permission_id', 'role_id']
        ])
            ->addColumn('permission_id', 'integer', ['signed' => false])
            ->addColumn('role_id', 'integer', ['signed' => false])
            ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE'])
            ->create();

        // Actualizamos la tabla users si no tiene role_id
        if (!$this->table('users')->hasColumn('role_id')) {
            $this->table('users')
                ->addColumn('role_id', 'integer', ['signed' => false, 'null' => true])
                ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'SET NULL'])
                ->update();
        }
    }
}
