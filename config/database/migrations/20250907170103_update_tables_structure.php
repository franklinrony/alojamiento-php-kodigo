<?php

use Phinx\Migration\AbstractMigration;

class UpdateTablesStructure extends AbstractMigration
{
    public function change()
    {
        if ($this->hasTable('user_activities')) {
            $table = $this->table('user_activities');
            // Si necesitas cambiar algo en la estructura de user_activities, hazlo aquí
        }

        if ($this->hasTable('user_preferences')) {
            $table = $this->table('user_preferences');
            // Si necesitas cambiar algo en la estructura de user_preferences, hazlo aquí
        }
    }
}
