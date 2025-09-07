<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBaseTables extends AbstractMigration
{
    public function change(): void
    {
        // Tabla de roles
        $this->table('roles')
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addTimestamps()
            ->create();

        // Tabla de permisos
        $this->table('permissions')
            ->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
            ->addTimestamps()
            ->create();

        // Tabla de usuarios
        $this->table('users')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('email', 'string', ['limit' => 150])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('role_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('active', 'boolean', ['default' => true])
            ->addTimestamps()
            ->addIndex(['email'], ['unique' => true])
            ->create();

        // Tabla de alojamientos
        $this->table('accommodations')
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('location', 'string', ['limit' => 150])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
            ->addTimestamps()
            ->create();
    }
}
