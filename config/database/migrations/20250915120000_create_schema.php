<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSchema extends AbstractMigration
{
    public function up(): void
    {
        // roles
        if (!$this->hasTable('roles')) {
            $this->table('roles')
                ->addColumn('name', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP'])
                ->create();
        }

        // permissions
        if (!$this->hasTable('permissions')) {
            $this->table('permissions')
                ->addColumn('name', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('description', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['name'], ['unique' => false])
                ->create();
        }

        // users
        if (!$this->hasTable('users')) {
            $this->table('users')
                ->addColumn('name', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('email', 'string', ['limit' => 150, 'null' => true])
                ->addColumn('password', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('role_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('active', 'boolean', ['default' => 1])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['email'], ['unique' => true])
                ->create();
        }
        // FK users.role_id -> roles.id
        if ($this->hasTable('users') && $this->hasTable('roles')) {
            $users = $this->table('users');
            // addForeignKey is idempotent if same definition; wrap in try/catch to ignore duplicates
            try {
                $users->addForeignKey('role_id', 'roles', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])->save();
            } catch (\Throwable $e) {
                // ignore if FK already exists
            }
        }

        // accommodations
        if (!$this->hasTable('accommodations')) {
            $this->table('accommodations')
                ->addColumn('name', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('description', 'text', ['null' => true])
                ->addColumn('location', 'string', ['limit' => 150, 'null' => true])
                ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
                ->addColumn('created_by', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP'])
                ->create();
        }
        if ($this->hasTable('accommodations') && $this->hasTable('users')) {
            try {
                $this->table('accommodations')
                    ->addForeignKey('created_by', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
                    ->save();
            } catch (\Throwable $e) {}
        }

        // permission_role pivot (composite PK)
        if (!$this->hasTable('permission_role')) {
            $this->table('permission_role', ['id' => false, 'primary_key' => ['permission_id', 'role_id']])
                ->addColumn('permission_id', 'integer', ['signed' => false])
                ->addColumn('role_id', 'integer', ['signed' => false])
                ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE'])
                ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE'])
                ->create();
        }

        // reservations
        if (!$this->hasTable('reservations')) {
            $this->table('reservations')
                ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('accommodation_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('check_in_date', 'date', ['null' => true])
                ->addColumn('check_out_date', 'date', ['null' => true])
                ->addColumn('total_price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
                ->addColumn('status', 'enum', ['values' => ['active', 'cancelled', 'completed'], 'default' => 'active', 'null' => true])
                ->addColumn('guests', 'integer', ['default' => 1, 'null' => true])
                ->addColumn('special_requests', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null, 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['check_in_date'])
                ->addIndex(['check_out_date'])
                ->addIndex(['status'])
                ->create();
        }
        if ($this->hasTable('reservations')) {
            try {
                $this->table('reservations')
                    ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL'])
                    ->addForeignKey('accommodation_id', 'accommodations', 'id', ['delete' => 'SET_NULL'])
                    ->save();
            } catch (\Throwable $e) {}
        }

        // user_activities
        if (!$this->hasTable('user_activities')) {
            $this->table('user_activities')
                ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('type', 'string', ['limit' => 50, 'null' => true])
                ->addColumn('details', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['user_id'])
                ->create();
        }
        if ($this->hasTable('user_activities')) {
            try {
                $this->table('user_activities')->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])->save();
            } catch (\Throwable $e) {}
        }

        // user_preferences (unique user_id)
        if (!$this->hasTable('user_preferences')) {
            $this->table('user_preferences')
                ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('notifications', 'string', ['limit' => 20, 'default' => 'all', 'null' => true])
                ->addColumn('email_updates', 'boolean', ['default' => 1, 'null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['user_id'], ['unique' => true])
                ->create();
        }
        if ($this->hasTable('user_preferences')) {
            try {
                $this->table('user_preferences')->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])->save();
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // Drop in reverse order of dependencies
        foreach (['user_preferences','user_activities','reservations','permission_role','accommodations','users','permissions','roles'] as $table) {
            if ($this->hasTable($table)) {
                $this->table($table)->drop()->save();
            }
        }
    }
}


