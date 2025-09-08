<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePermissionsTables extends AbstractMigration
{
    public function change(): void
    {
        // Tabla de permisos
        $this->table('permissions')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'string', ['limit' => 255])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        // Tabla pivot permission_role
        $this->table('permission_role')
            ->addColumn('permission_id', 'integer')
            ->addColumn('role_id', 'integer')
            ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
