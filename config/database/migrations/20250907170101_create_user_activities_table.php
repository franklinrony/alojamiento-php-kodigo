<?php

use Phinx\Migration\AbstractMigration;

class CreateUserActivitiesTable extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('user_activities')) {
            $table = $this->table('user_activities');
            $table->addColumn('user_id', 'integer', ['signed' => false])
                ->addColumn('type', 'string', ['limit' => 50])
                ->addColumn('details', 'text', ['null' => true])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
                ->create();
        }
    }
}
