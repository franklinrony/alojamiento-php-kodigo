<?php

namespace App\Controllers\Api;

use App\Services\IAccommodationService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class AccommodationApiController
 * Controlador API para gestión de alojamientos
 */
class AccommodationApiController extends BaseApiController
{
    /**
     * @var IAccommodationService
     */
    private $accommodationService;

    /**
     * @param IAccommodationService $accommodationService
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     */
    public function __construct(
        IAccommodationService $accommodationService,
        IRequestValidator $validator,
        IAuthenticator $authenticator
    ) {
        parent::__construct($validator, $authenticator);
        $this->accommodationService = $accommodationService;
    }

    /**
     * Lista todos los alojamientos con filtros opcionales y paginación
     */
    public function index(): void
    {
        try {
            $criteria = [];

            // Aplicar filtros si existen
            if ($location = $_GET['location'] ?? null) {
                $criteria['location'] = $location;
            }

            if ($minPrice = $_GET['min_price'] ?? null) {
                $criteria['minPrice'] = (float) $minPrice;
            }

            if ($maxPrice = $_GET['max_price'] ?? null) {
                $criteria['maxPrice'] = (float) $maxPrice;
            }

            // Paginación
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 12);
            $offset = ($page - 1) * $limit;

            $criteria['limit'] = $limit;
            $criteria['offset'] = $offset;

            $accommodations = $this->accommodationService->searchAccommodations($criteria);
            
            // Obtener total para paginación
            $totalAccommodations = $this->accommodationService->countAccommodations($criteria);
            $totalPages = ceil($totalAccommodations / $limit);

            // Convertir objetos a arrays para JSON
            $accommodationsData = array_map(function($accommodation) {
                return [
                    'id' => $accommodation->getId(),
                    'name' => $accommodation->getName(),
                    'description' => $accommodation->getDescription(),
                    'location' => $accommodation->getLocation(),
                    'price' => $accommodation->getPrice(),
                    'created_at' => $accommodation->getCreatedAt(),
                    'updated_at' => $accommodation->getUpdatedAt()
                ];
            }, $accommodations);

            $this->success([
                'accommodations' => $accommodationsData,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $totalAccommodations,
                    'items_per_page' => $limit,
                    'has_previous' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ], 'Alojamientos obtenidos exitosamente');

        } catch (\Exception $e) {
            $this->error('Error al obtener alojamientos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Muestra un alojamiento específico
     */
    public function show(int $id): void
    {
        try {
            $accommodation = $this->accommodationService->getAccommodation($id);

            if (!$accommodation) {
                $this->error('Alojamiento no encontrado', 404);
                return;
            }

            $accommodationData = [
                'id' => $accommodation->getId(),
                'name' => $accommodation->getName(),
                'description' => $accommodation->getDescription(),
                'location' => $accommodation->getLocation(),
                'price' => $accommodation->getPrice(),
                'created_at' => $accommodation->getCreatedAt(),
                'updated_at' => $accommodation->getUpdatedAt()
            ];

            $this->success($accommodationData, 'Alojamiento obtenido exitosamente');

        } catch (\Exception $e) {
            $this->error('Error al obtener alojamiento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crea un nuevo alojamiento
     */
    public function create(): void
    {
        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        try {
            $rules = [
                'name' => ['required' => true, 'type' => 'string', 'min' => 2],
                'description' => ['required' => true, 'type' => 'string', 'min' => 10],
                'location' => ['required' => true, 'type' => 'string', 'min' => 2],
                'price' => ['required' => true, 'type' => 'numeric', 'min' => 0]
            ];

            $data = $this->getJsonRequest($rules);

            $accommodation = $this->accommodationService->createAccommodation(
                $data,
                $this->authenticator->getUserId()
            );

            $accommodationData = [
                'id' => $accommodation->getId(),
                'name' => $accommodation->getName(),
                'description' => $accommodation->getDescription(),
                'location' => $accommodation->getLocation(),
                'price' => $accommodation->getPrice(),
                'created_at' => $accommodation->getCreatedAt(),
                'updated_at' => $accommodation->getUpdatedAt()
            ];

            $this->success($accommodationData, 'Alojamiento creado exitosamente', 201);

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Actualiza un alojamiento existente
     */
    public function update(int $id): void
    {
        if (!$this->isMethod('PUT')) {
            $this->error('Método no permitido', 405);
            return;
        }

        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        try {
            $rules = [
                'name' => ['type' => 'string', 'min' => 2],
                'description' => ['type' => 'string', 'min' => 10],
                'location' => ['type' => 'string', 'min' => 2],
                'price' => ['type' => 'numeric', 'min' => 0]
            ];

            $data = $this->getJsonRequest($rules);

            $accommodation = $this->accommodationService->updateAccommodation(
                $id,
                $data,
                $this->authenticator->getUserId()
            );

            if (!$accommodation) {
                $this->error('Alojamiento no encontrado o no tienes permisos para editarlo', 404);
                return;
            }

            $accommodationData = [
                'id' => $accommodation->getId(),
                'name' => $accommodation->getName(),
                'description' => $accommodation->getDescription(),
                'location' => $accommodation->getLocation(),
                'price' => $accommodation->getPrice(),
                'created_at' => $accommodation->getCreatedAt(),
                'updated_at' => $accommodation->getUpdatedAt()
            ];

            $this->success($accommodationData, 'Alojamiento actualizado exitosamente');

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Elimina un alojamiento
     */
    public function delete(int $id): void
    {
        if (!$this->isMethod('DELETE')) {
            $this->error('Método no permitido', 405);
            return;
        }

        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        try {
            $success = $this->accommodationService->deleteAccommodation($id, $this->authenticator->getUserId());

            if (!$success) {
                $this->error('Alojamiento no encontrado o no tienes permisos para eliminarlo', 404);
                return;
            }

            $this->success(null, 'Alojamiento eliminado exitosamente');

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }
}
