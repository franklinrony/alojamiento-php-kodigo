<?php

namespace App\Controllers;

use App\Services\IAccommodationService;

/**
 * Class AccommodationController
 * Controlador para gestión de alojamientos
 */
class AccommodationController extends BaseController
{
    /**
     * @var IAccommodationService
     */
    private $accommodationService;

    /**
     * @param IAccommodationService $accommodationService
     */
    public function __construct(IAccommodationService $accommodationService)
    {
        $this->accommodationService = $accommodationService;
    }

    /**
     * Lista todos los alojamientos con filtros opcionales
     */
    public function index(): void
    {
        $criteria = [];

        // Aplicar filtros si existen
        if ($location = $this->getParam('location')) {
            $criteria['location'] = $location;
        }

        if ($minPrice = $this->getParam('min_price')) {
            $criteria['minPrice'] = (float) $minPrice;
        }

        if ($maxPrice = $this->getParam('max_price')) {
            $criteria['maxPrice'] = (float) $maxPrice;
        }

        $accommodations = $this->accommodationService->searchAccommodations($criteria);
        $this->jsonResponse(['accommodations' => $accommodations]);
    }

    /**
     * Crea un nuevo alojamiento
     */
    public function create(): void
    {
        if (!$this->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('POST')) {
            $this->error('Método no permitido', 405);
            return;
        }

        $data = $this->getJsonRequest();
        
        if (!isset($data['name']) || !isset($data['location']) || !isset($data['price'])) {
            $this->error('Datos incompletos');
            return;
        }

        try {
            $accommodation = $this->accommodationService->createAccommodation(
                $data,
                $this->getAuthUserId()
            );

            $this->jsonResponse([
                'message' => 'Alojamiento creado exitosamente',
                'accommodation' => $accommodation
            ], 201);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Actualiza un alojamiento existente
     *
     * @param int $id
     */
    public function update(int $id): void
    {
        if (!$this->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('PUT')) {
            $this->error('Método no permitido', 405);
            return;
        }

        try {
            $data = $this->getJsonRequest();
            $accommodation = $this->accommodationService->updateAccommodation(
                $id,
                $data,
                $this->getAuthUserId()
            );

            if (!$accommodation) {
                $this->error('Alojamiento no encontrado', 404);
                return;
            }

            $this->jsonResponse([
                'message' => 'Alojamiento actualizado exitosamente',
                'accommodation' => $accommodation
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 403);
        }
    }

    /**
     * Elimina un alojamiento
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        if (!$this->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        if (!$this->isMethod('DELETE')) {
            $this->error('Método no permitido', 405);
            return;
        }

        try {
            $success = $this->accommodationService->deleteAccommodation(
                $id,
                $this->getAuthUserId()
            );

            if (!$success) {
                $this->error('Error al eliminar el alojamiento', 400);
                return;
            }

            $this->jsonResponse([
                'message' => 'Alojamiento eliminado exitosamente'
            ]);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 403);
        }
    }

    /**
     * Obtiene un alojamiento específico
     *
     * @param int $id
     */
    public function show(int $id): void
    {
        $accommodation = $this->accommodationService->getAccommodation($id);

        if (!$accommodation) {
            $this->error('Alojamiento no encontrado', 404);
            return;
        }

        $this->jsonResponse(['accommodation' => $accommodation]);
    }
}
