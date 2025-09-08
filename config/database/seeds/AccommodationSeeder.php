<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AccommodationSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Limpiar datos existentes primero
        $this->execute('DELETE FROM accommodations');

        // Obtener el ID del usuario admin
        $adminUser = $this->fetchRow("SELECT id FROM users WHERE email = 'admin@demo.com'");
        if (!$adminUser) {
            throw new \Exception('Usuario admin no encontrado. Ejecuta UserSeeder primero.');
        }
        $adminId = $adminUser['id'];

        // Alojamientos de ejemplo
        $accommodations = [
            [
                'name' => 'Casa de Playa en Cancún',
                'description' => 'Hermosa casa frente al mar con vista panorámica al Caribe. Perfecta para familias y grupos. Incluye piscina privada, terraza con hamacas y acceso directo a la playa.',
                'location' => 'Cancún, Quintana Roo',
                'price' => 250.00,
                'user_id' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Departamento en Polanco',
                'description' => 'Moderno departamento en el corazón de Polanco. Cerca de restaurantes, tiendas y transporte público. Ideal para viajes de negocios o turismo urbano.',
                'location' => 'Polanco, Ciudad de México',
                'price' => 180.00,
                'user_id' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Cabaña en Valle de Bravo',
                'description' => 'Acogedora cabaña de madera en el bosque. Perfecta para desconectarse y disfrutar de la naturaleza. Incluye chimenea, terraza y vista al lago.',
                'location' => 'Valle de Bravo, Estado de México',
                'price' => 120.00,
                'user_id' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Loft Industrial en Roma Norte',
                'description' => 'Espacioso loft con diseño industrial en el barrio de Roma Norte. Techos altos, ventanales grandes y decoración moderna. Cerca de cafés y galerías.',
                'location' => 'Roma Norte, Ciudad de México',
                'price' => 200.00,
                'user_id' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Villa en Tulum',
                'description' => 'Lujosa villa con arquitectura maya moderna. Piscina infinity, jardín tropical y acceso privado a la playa. Incluye servicio de conserjería 24/7.',
                'location' => 'Tulum, Quintana Roo',
                'price' => 450.00,
                'user_id' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Casa Colonial en San Miguel',
                'description' => 'Auténtica casa colonial restaurada en el centro histórico. Patio central, fuentes y decoración tradicional mexicana. A pasos de la parroquia principal.',
                'location' => 'San Miguel de Allende, Guanajuato',
                'price' => 150.00,
                'user_id' => $adminId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('accommodations')->insert($accommodations)->saveData();
    }
}
