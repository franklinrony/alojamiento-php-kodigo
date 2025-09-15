<?php

namespace App\Controllers\Api;

use App\Services\IReservationService;
use App\Services\IAccommodationService;
use App\Utilities\IRequestValidator;
use App\Utilities\IAuthenticator;

/**
 * Class ReservationApiController
 * Controlador API para gestión de reservas
 */
class ReservationApiController extends BaseApiController
{
    /**
     * @var IReservationService
     */
    private $reservationService;

    /**
     * @var IAccommodationService
     */
    private $accommodationService;

    /**
     * @param IReservationService $reservationService
     * @param IAccommodationService $accommodationService
     * @param IRequestValidator $validator
     * @param IAuthenticator $authenticator
     */
    public function __construct(
        IReservationService $reservationService,
        IAccommodationService $accommodationService,
        IRequestValidator $validator,
        IAuthenticator $authenticator
    ) {
        parent::__construct($validator, $authenticator);
        $this->reservationService = $reservationService;
        $this->accommodationService = $accommodationService;
    }

    /**
     * Lista las reservas del usuario autenticado
     */
    public function index(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        try {
            $userId = $this->authenticator->getUserId();
            
            // Filtros opcionales
            $status = $_GET['status'] ?? null;
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $reservations = $this->reservationService->getReservationsByUser($userId);
            $totalReservations = count($reservations);
            $totalPages = ceil($totalReservations / $limit);

            // Convertir objetos a arrays para JSON
            $reservationsData = array_map(function($reservation) {
                return [
                    'id' => $reservation->getId(),
                    'accommodation_id' => $reservation->getAccommodationId(),
                    'accommodation_name' => $reservation->getAccommodation() ? $reservation->getAccommodation()->getName() : 'N/A',
                    'check_in_date' => $reservation->getCheckInDate(),
                    'check_out_date' => $reservation->getCheckOutDate(),
                    'guests' => $reservation->getGuests(),
                    'total_price' => $reservation->getTotalPrice(),
                    'status' => $reservation->getStatus(),
                    'created_at' => $reservation->getCreatedAt(),
                    'updated_at' => $reservation->getUpdatedAt()
                ];
            }, $reservations);

            $this->success([
                'reservations' => $reservationsData,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $totalReservations,
                    'items_per_page' => $limit,
                    'has_previous' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ], 'Reservas obtenidas exitosamente');

        } catch (\Exception $e) {
            $this->error('Error al obtener reservas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Muestra una reserva específica
     */
    public function show(int $id): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        try {
            $userId = $this->authenticator->getUserId();
            $reservation = $this->reservationService->getReservationById($id, $userId);

            if (!$reservation) {
                $this->error('Reserva no encontrada o no tienes permisos para verla', 404);
                return;
            }

            $reservationData = [
                'id' => $reservation->getId(),
                'accommodation_id' => $reservation->getAccommodationId(),
                'accommodation_name' => $reservation->getAccommodation() ? $reservation->getAccommodation()->getName() : 'N/A',
                'check_in_date' => $reservation->getCheckInDate(),
                'check_out_date' => $reservation->getCheckOutDate(),
                'guests' => $reservation->getGuests(),
                'total_price' => $reservation->getTotalPrice(),
                'status' => $reservation->getStatus(),
                'created_at' => $reservation->getCreatedAt(),
                'updated_at' => $reservation->getUpdatedAt()
            ];

            $this->success($reservationData, 'Reserva obtenida exitosamente');

        } catch (\Exception $e) {
            $this->error('Error al obtener reserva: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crea una nueva reserva
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
                'accommodation_id' => ['required' => true, 'type' => 'integer'],
                'check_in_date' => ['required' => true, 'type' => 'string'],
                'check_out_date' => ['required' => true, 'type' => 'string'],
                'guests' => ['type' => 'integer', 'min' => 1, 'max' => 20]
            ];

            $data = $this->getJsonRequest($rules);
            $data['user_id'] = $this->authenticator->getUserId();

            // Validar que el alojamiento existe
            $accommodation = $this->accommodationService->getAccommodation($data['accommodation_id']);
            if (!$accommodation) {
                $this->error('Alojamiento no encontrado', 404);
                return;
            }

            // Verificar disponibilidad
            $available = $this->reservationService->isAccommodationAvailable(
                $data['accommodation_id'],
                $data['check_in_date'],
                $data['check_out_date']
            );

            if (!$available) {
                $this->error('El alojamiento no está disponible para las fechas seleccionadas', 400);
                return;
            }

            $reservation = $this->reservationService->createReservation($data);

            if (!$reservation) {
                $this->error('Error al crear la reserva', 500);
                return;
            }

            $reservationData = [
                'id' => $reservation->getId(),
                'accommodation_id' => $reservation->getAccommodationId(),
                'accommodation_name' => $reservation->getAccommodation() ? $reservation->getAccommodation()->getName() : 'N/A',
                'check_in_date' => $reservation->getCheckInDate(),
                'check_out_date' => $reservation->getCheckOutDate(),
                'guests' => $reservation->getGuests(),
                'total_price' => $reservation->getTotalPrice(),
                'status' => $reservation->getStatus(),
                'created_at' => $reservation->getCreatedAt(),
                'updated_at' => $reservation->getUpdatedAt()
            ];

            $this->success($reservationData, 'Reserva creada exitosamente', 201);

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Actualiza una reserva existente
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
                'check_in_date' => ['type' => 'string'],
                'check_out_date' => ['type' => 'string'],
                'guests' => ['type' => 'integer', 'min' => 1, 'max' => 20],
                'status' => ['type' => 'string', 'in' => ['pending', 'confirmed', 'cancelled', 'completed']]
            ];

            $data = $this->getJsonRequest($rules);
            $userId = $this->authenticator->getUserId();

            // Verificar que la reserva existe y pertenece al usuario
            $existingReservation = $this->reservationService->getReservationById($id, $userId);
            if (!$existingReservation) {
                $this->error('Reserva no encontrada o no tienes permisos para editarla', 404);
                return;
            }

            // Si se están cambiando las fechas, verificar disponibilidad
            if (isset($data['check_in_date']) || isset($data['check_out_date'])) {
                $checkIn = $data['check_in_date'] ?? $existingReservation->getCheckInDate();
                $checkOut = $data['check_out_date'] ?? $existingReservation->getCheckOutDate();
                
                $available = $this->reservationService->isAccommodationAvailable(
                    $existingReservation->getAccommodationId(),
                    $checkIn,
                    $checkOut,
                    $id // Excluir la reserva actual de la verificación
                );

                if (!$available) {
                    $this->error('El alojamiento no está disponible para las fechas seleccionadas', 400);
                    return;
                }
            }

            // For now, we'll return an error as updateReservation method doesn't exist
            $this->error('Actualización de reservas no implementada aún', 501);
            return;

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Cancela una reserva
     */
    public function cancel(int $id): void
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
            $userId = $this->authenticator->getUserId();
            $success = $this->reservationService->cancelReservation($id, $userId);

            if (!$success) {
                $this->error('Error al cancelar la reserva. Verifica que la reserva exista y esté activa.', 400);
                return;
            }

            $this->success(null, 'Reserva cancelada exitosamente');

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Error interno del servidor', 500);
        }
    }

    /**
     * Verifica disponibilidad de un alojamiento
     */
    public function checkAvailability(int $accommodationId): void
    {
        try {
            $checkIn = $_GET['check_in'] ?? null;
            $checkOut = $_GET['check_out'] ?? null;

            if (!$checkIn || !$checkOut) {
                $this->error('Fechas de entrada y salida requeridas', 400);
                return;
            }

            // Validar que el alojamiento existe
            $accommodation = $this->accommodationService->getAccommodation($accommodationId);
            if (!$accommodation) {
                $this->error('Alojamiento no encontrado', 404);
                return;
            }

            $available = $this->reservationService->isAccommodationAvailable($accommodationId, $checkIn, $checkOut);
            
            $this->success([
                'available' => $available,
                'accommodation_id' => $accommodationId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'message' => $available ? 'Disponible' : 'No disponible para las fechas seleccionadas'
            ], 'Disponibilidad verificada');

        } catch (\Exception $e) {
            $this->error('Error al verificar disponibilidad: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene estadísticas de reservas del usuario
     */
    public function stats(): void
    {
        if (!$this->authenticator->isAuthenticated()) {
            $this->error('No autorizado', 401);
            return;
        }

        try {
            $userId = $this->authenticator->getUserId();
            $stats = $this->reservationService->getReservationStats($userId);

            $this->success($stats, 'Estadísticas obtenidas exitosamente');

        } catch (\Exception $e) {
            $this->error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}
