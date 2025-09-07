<?php

namespace App\Services\Implementations;

use App\Models\Accommodation;
use App\Repositories\IAccommodationRepository;
use App\Services\IUserService;
use App\Services\IAccommodationService;

/**
 * Class AccommodationService
 * Implementación del servicio de alojamientos
 */
class AccommodationService implements IAccommodationService
{
    /**
     * @var IAccommodationRepository
     */
    private $accommodationRepository;

    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @param IAccommodationRepository $accommodationRepository
     * @param IUserService $userService
     */
    public function __construct(
        IAccommodationRepository $accommodationRepository,
        IUserService $userService
    ) {
        $this->accommodationRepository = $accommodationRepository;
        $this->userService = $userService;
    }

    /**
     * @inheritDoc
     */
    public function createAccommodation(array $accommodationData, int $creatorId): Accommodation
    {
        // Verificar si el usuario tiene permiso para crear alojamientos
        if (!$this->userService->hasPermission($creatorId, 'create_accommodation')) {
            throw new \RuntimeException('No tiene permiso para crear alojamientos');
        }

        $accommodationData['created_by'] = $creatorId;
        return $this->accommodationRepository->create($accommodationData);
    }

    /**
     * @inheritDoc
     */
    public function updateAccommodation(int $accommodationId, array $accommodationData, int $userId): ?Accommodation
    {
        if (!$this->canManageAccommodation($userId, $accommodationId)) {
            throw new \RuntimeException('No tiene permiso para editar este alojamiento');
        }

        return $this->accommodationRepository->update($accommodationId, $accommodationData);
    }

    /**
     * @inheritDoc
     */
    public function deleteAccommodation(int $accommodationId, int $userId): bool
    {
        if (!$this->canManageAccommodation($userId, $accommodationId)) {
            throw new \RuntimeException('No tiene permiso para eliminar este alojamiento');
        }

        return $this->accommodationRepository->delete($accommodationId);
    }

    /**
     * @inheritDoc
     */
    public function searchAccommodations(array $criteria): array
    {
        // Buscar por rango de precios si se especifica
        if (isset($criteria['minPrice']) && isset($criteria['maxPrice'])) {
            return $this->accommodationRepository->findByPriceRange(
                (float) $criteria['minPrice'],
                (float) $criteria['maxPrice']
            );
        }

        // Buscar por ubicación si se especifica
        if (isset($criteria['location'])) {
            return $this->accommodationRepository->findByLocation($criteria['location']);
        }

        // Si no hay criterios específicos, devolver todos
        return $this->accommodationRepository->all();
    }

    /**
     * @inheritDoc
     */
    public function getUserAccommodations(int $userId): array
    {
        return $this->accommodationRepository->findByCreator($userId);
    }

    /**
     * @inheritDoc
     */
    public function canManageAccommodation(int $userId, int $accommodationId): bool
    {
        $accommodation = $this->accommodationRepository->find($accommodationId);
        if (!$accommodation) {
            return false;
        }

        // El usuario puede gestionar el alojamiento si:
        // 1. Es el creador del alojamiento
        // 2. Tiene el permiso de administrar todos los alojamientos
        return $accommodation->getCreatedBy() === $userId ||
               $this->userService->hasPermission($userId, 'manage_all_accommodations');
    }

    /**
     * @inheritDoc
     */
    public function getAccommodation(int $id): ?Accommodation
    {
        return $this->accommodationRepository->find($id);
    }
}
