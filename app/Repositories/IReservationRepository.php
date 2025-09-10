<?php

namespace App\Repositories;

use App\Models\Reservation;

/**
 * Interface IReservationRepository
 * Interfaz para el repositorio de reservas
 */
interface IReservationRepository extends IRepository
{
    /**
     * Busca reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array;

    /**
     * Busca reservas por alojamiento
     *
     * @param int $accommodationId
     * @return array
     */
    public function findByAccommodationId(int $accommodationId): array;

    /**
     * Busca reservas activas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function findActiveByUserId(int $userId): array;

    /**
     * Busca reservas por rango de fechas
     *
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $accommodationId
     * @return array
     */
    public function findByDateRange(string $checkIn, string $checkOut, ?int $accommodationId = null): array;

    /**
     * Verifica si hay conflictos de fechas para un alojamiento
     *
     * @param int $accommodationId
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $excludeReservationId
     * @return bool
     */
    public function hasDateConflict(int $accommodationId, string $checkIn, string $checkOut, ?int $excludeReservationId = null): bool;

    /**
     * Busca reservas por estado
     *
     * @param string $status
     * @return array
     */
    public function findByStatus(string $status): array;

    /**
     * Obtiene estadísticas de reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getStatsByUserId(int $userId): array;
}
