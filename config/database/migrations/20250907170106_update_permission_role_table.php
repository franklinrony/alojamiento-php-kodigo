<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdatePermissionRoleTable extends AbstractMigration
{
    public function change(): void
    {
        $exists = $this->hasTable('permission_role');
        
        if ($exists) {
            $this->table('permission_role')
                ->addColumn('permission_id', 'integer', ['signed' => false])
                ->addColumn('role_id', 'integer', ['signed' => false])
                ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE'])
                ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE'])
                ->update();
        }
    }
}
