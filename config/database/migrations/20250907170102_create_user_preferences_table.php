<?php

use Phinx\Migration\AbstractMigration;

class CreateUserPreferencesTable extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('user_preferences')) {
            $table = $this->table('user_preferences');
            $table->addColumn('user_id', 'integer', ['signed' => false])
                ->addColumn('notifications', 'string', ['limit' => 20, 'default' => 'all'])
                ->addColumn('email_updates', 'boolean', ['default' => true])
                ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
                ->addIndex(['user_id'], ['unique' => true])
                ->create();
        }
    }
}
