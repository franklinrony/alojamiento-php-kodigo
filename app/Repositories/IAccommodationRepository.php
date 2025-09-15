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
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findByPriceRange(float $minPrice, float $maxPrice, ?int $limit = null, ?int $offset = null);

    /**
     * Busca alojamientos por ubicación
     *
     * @param string $location
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findByLocation(string $location, ?int $limit = null, ?int $offset = null);

    /**
     * Obtiene los alojamientos creados por un usuario
     *
     * @param int $userId
     * @return array
     */
    public function findByCreator(int $userId);

    /**
     * Cuenta alojamientos por rango de precios
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @return int
     */
    public function countByPriceRange(float $minPrice, float $maxPrice): int;

    /**
     * Cuenta alojamientos por ubicación
     *
     * @param string $location
     * @return int
     */
    public function countByLocation(string $location): int;

    /**
     * Cuenta todos los alojamientos
     *
     * @return int
     */
    public function count(): int;
}
