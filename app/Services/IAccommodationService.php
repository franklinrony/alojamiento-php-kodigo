<?php

namespace App\Services;

use App\Models\Accommodation;

/**
 * Interface IAccommodationService
 * Interfaz para el servicio de alojamientos
 */
interface IAccommodationService
{
    /**
     * Crea un nuevo alojamiento
     *
     * @param array $accommodationData
     * @param int $creatorId
     * @return Accommodation
     */
    public function createAccommodation(array $accommodationData, int $creatorId): Accommodation;

    /**
     * Actualiza un alojamiento existente
     *
     * @param int $accommodationId
     * @param array $accommodationData
     * @param int $userId ID del usuario que intenta hacer la actualización
     * @return Accommodation|null
     * @throws \RuntimeException si el usuario no tiene permiso para editar
     */
    public function updateAccommodation(int $accommodationId, array $accommodationData, int $userId): ?Accommodation;

    /**
     * Elimina un alojamiento
     *
     * @param int $accommodationId
     * @param int $userId ID del usuario que intenta eliminar
     * @return bool
     * @throws \RuntimeException si el usuario no tiene permiso para eliminar
     */
    public function deleteAccommodation(int $accommodationId, int $userId): bool;

    /**
     * Busca alojamientos por criterios
     *
     * @param array $criteria Puede incluir: location, minPrice, maxPrice
     * @return Accommodation[]
     */
    public function searchAccommodations(array $criteria): array;

    /**
     * Obtiene los alojamientos creados por un usuario
     *
     * @param int $userId
     * @return Accommodation[]
     */
    public function getUserAccommodations(int $userId): array;

    /**
     * Verifica si un usuario tiene permisos sobre un alojamiento
     *
     * @param int $userId
     * @param int $accommodationId
     * @return bool
     */
    public function canManageAccommodation(int $userId, int $accommodationId): bool;
}
