<?php

namespace App\Services;

use App\Models\Reservation;

/**
 * Interface IReservationService
 * Interfaz para el servicio de reservas
 */
interface IReservationService
{
    /**
     * Crea una nueva reserva
     *
     * @param array $data
     * @return Reservation|null
     */
    public function createReservation(array $data): ?Reservation;

    /**
     * Cancela una reserva
     *
     * @param int $reservationId
     * @param int $userId
     * @return bool
     */
    public function cancelReservation(int $reservationId, int $userId): bool;

    /**
     * Obtiene reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getReservationsByUser(int $userId): array;

    /**
     * Obtiene reservas activas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getActiveReservationsByUser(int $userId): array;

    /**
     * Obtiene estadísticas de reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getReservationStats(int $userId): array;

    /**
     * Obtiene una reserva por ID
     *
     * @param int $reservationId
     * @param int|null $userId
     * @return Reservation|null
     */
    public function getReservationById(int $reservationId, ?int $userId = null): ?Reservation;

    /**
     * Verifica disponibilidad de un alojamiento
     *
     * @param int $accommodationId
     * @param string $checkIn
     * @param string $checkOut
     * @return bool
     */
    public function isAccommodationAvailable(int $accommodationId, string $checkIn, string $checkOut): bool;

    /**
     * Calcula el precio total de una reserva
     *
     * @param int $accommodationId
     * @param string $checkIn
     * @param string $checkOut
     * @param int $guests
     * @return float
     */
    public function calculateTotalPrice(int $accommodationId, string $checkIn, string $checkOut, int $guests = 1): float;

    /**
     * Valida los datos de una reserva
     *
     * @param array $data
     * @return array
     */
    public function validateReservationData(array $data): array;
}
