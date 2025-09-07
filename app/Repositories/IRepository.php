<?php

namespace App\Repositories;

/**
 * Interface IRepository
 * Interfaz base para todos los repositorios
 */
interface IRepository
{
    /**
     * Encuentra un registro por su ID
     *
     * @param int $id
     * @return mixed
     */
    public function find($id);

    /**
     * Obtiene todos los registros
     *
     * @return array
     */
    public function all();

    /**
     * Crea un nuevo registro
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Actualiza un registro
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Elimina un registro
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
