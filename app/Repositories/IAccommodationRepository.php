<?php

namespace App\Repositories;

/**
 * Interface IAccommodationRepository
 * Interfaz para el repositorio de alojamientos
 */
interface IAccommodationRepository extends IRepository
{
    /**
     * Busca alojamientos por rango de precio
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return array
     */
    public function findByPriceRange(float $minPrice, float $maxPrice);

    /**
     * Busca alojamientos por ubicación
     *
     * @param string $location
     * @return array
     */
    public function findByLocation(string $location);

    /**
     * Obtiene los alojamientos creados por un usuario
     *
     * @param int $userId
     * @return array
     */
    public function findByCreator(int $userId);
}
