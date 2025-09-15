<?php

use Phinx\Migration\AbstractMigration;

class RemoveUserAccommodationsTable extends AbstractMigration
{
    public function change()
    {
        // Eliminar la tabla user_accommodations ya que es redundante
        // La relación usuario-alojamiento se maneja a través de reservations
        if ($this->hasTable('user_accommodations')) {
            $this->table('user_accommodations')->drop()->save();
        }
    }
}
