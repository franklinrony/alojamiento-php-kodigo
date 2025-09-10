<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateReservationsTable extends AbstractMigration
{
    public function change(): void
    {
        // Tabla de reservas
        $this->table('reservations')
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('accommodation_id', 'integer', ['signed' => false])
            ->addColumn('check_in_date', 'date')
            ->addColumn('check_out_date', 'date')
            ->addColumn('total_price', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('status', 'enum', [
                'values' => ['active', 'cancelled', 'completed'],
                'default' => 'active'
            ])
            ->addColumn('guests', 'integer', ['default' => 1])
            ->addColumn('special_requests', 'text', ['null' => true])
            ->addTimestamps()
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('accommodation_id', 'accommodations', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addIndex(['user_id'])
            ->addIndex(['accommodation_id'])
            ->addIndex(['check_in_date'])
            ->addIndex(['check_out_date'])
            ->addIndex(['status'])
            ->create();
    }
}
