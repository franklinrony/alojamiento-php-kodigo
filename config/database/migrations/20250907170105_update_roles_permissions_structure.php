<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateRolesPermissionsStructure extends AbstractMigration
{
    public function change(): void
    {
        // Add role_id to users table if not exists
        if (!$this->table('users')->hasColumn('role_id')) {
            $this->table('users')
                ->addColumn('role_id', 'integer', ['null' => true])
                ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'SET NULL'])
                ->update();
        }
    }
}
