<?php

namespace App\Services\Implementations;

use App\Models\Reservation;
use App\Models\Accommodation;
use App\Repositories\IReservationRepository;
use App\Repositories\IAccommodationRepository;
use App\Services\IReservationService;
use App\Services\ILoggerService;
use Exception;

/**
 * Class ReservationService
 * Implementación del servicio de reservas
 */
class ReservationService implements IReservationService
{
    /**
     * @var IReservationRepository
     */
    private $reservationRepository;

    /**
     * @var IAccommodationRepository
     */
    private $accommodationRepository;

    /**
     * @var ILoggerService
     */
    private $logger;

    /**
     * Constructor
     *
     * @param IReservationRepository $reservationRepository
     * @param IAccommodationRepository $accommodationRepository
     * @param ILoggerService $logger
     */
    public function __construct(
        IReservationRepository $reservationRepository,
        IAccommodationRepository $accommodationRepository,
        ILoggerService $logger
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->accommodationRepository = $accommodationRepository;
        $this->logger = $logger;
    }

    /**
     * Crea una nueva reserva
     *
     * @param array $data
     * @return Reservation|null
     */
    public function createReservation(array $data): ?Reservation
    {
        try {
            // Validar datos
            $validation = $this->validateReservationData($data);
            if (!empty($validation['errors'])) {
                $this->logger->warning("Validation errors in reservation creation", [
                    'errors' => $validation['errors'],
                    'user_id' => $data['user_id'] ?? 'unknown',
                    'accommodation_id' => $data['accommodation_id']
                ]);
                return null;
            }

            // Verificar disponibilidad (deshabilitado temporalmente para permitir overbooking)
            // TODO: Implementar validación de disponibilidad más sofisticada si es necesario
            /*
            if (!$this->isAccommodationAvailable($data['accommodation_id'], $data['check_in_date'], $data['check_out_date'])) {
                $this->logger->warning("Accommodation not available for selected dates", [
                    'accommodation_id' => $data['accommodation_id'],
                    'check_in_date' => $data['check_in_date'],
                    'check_out_date' => $data['check_out_date'],
                    'user_id' => $userId
                ]);
                return null;
            }
            */

            // Calcular precio total
            $totalPrice = $this->calculateTotalPrice(
                $data['accommodation_id'],
                $data['check_in_date'],
                $data['check_out_date'],
                $data['guests'] ?? 1
            );

            // Crear la reserva
            $reservation = new Reservation();
            $reservation->setUserId($data['user_id']);
            $reservation->setAccommodationId($data['accommodation_id']);
            $reservation->setCheckInDate($data['check_in_date']);
            $reservation->setCheckOutDate($data['check_out_date']);
            $reservation->setTotalPrice($totalPrice);
            $reservation->setStatus('active');
            $reservation->setGuests($data['guests'] ?? 1);
            $reservation->setSpecialRequests($data['special_requests'] ?? null);

            // Guardar en la base de datos
            $data = $reservation->toArray();
            unset($data['id']); // Remover ID para la creación
            $savedReservation = $this->reservationRepository->create($data);
            
            if ($savedReservation) {
                // Log de la actividad
                $this->logger->logReservationActivity(
                    $data['user_id'], 
                    $savedReservation->getId(), 
                    'created',
                    [
                        'accommodation_id' => $data['accommodation_id'],
                        'check_in_date' => $data['check_in_date'],
                        'check_out_date' => $data['check_out_date'],
                        'guests' => $data['guests'] ?? 1,
                        'total_price' => $totalPrice
                    ]
                );
                return $savedReservation;
            }

            return null;
        } catch (Exception $e) {
            $this->logger->error("Error creating reservation", [
                'user_id' => $data['user_id'] ?? 'unknown',
                'accommodation_id' => $data['accommodation_id'] ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Cancela una reserva
     *
     * @param int $reservationId
     * @param int $userId
     * @return bool
     */
    public function cancelReservation(int $reservationId, int $userId): bool
    {
        try {
            $reservation = $this->getReservationById($reservationId, $userId);
            
            if (!$reservation) {
                $this->logger->warning("Reservation not found or user not authorized", [
                    'reservation_id' => $reservationId,
                    'user_id' => $userId
                ]);
                return false;
            }

            if (!$reservation->isActive()) {
                $this->logger->warning("Reservation is not active and cannot be cancelled", [
                    'reservation_id' => $reservationId,
                    'user_id' => $userId,
                    'status' => $reservation->getStatus()
                ]);
                return false;
            }

            // Actualizar estado
            $reservation->setStatus('cancelled');
            $result = $this->reservationRepository->update($reservation->getId(), ['status' => 'cancelled']);

            if ($result) {
                // Log de la actividad
                $this->logger->logReservationActivity(
                    $userId, 
                    $reservationId, 
                    'cancelled',
                    [
                        'accommodation_id' => $reservation->getAccommodationId(),
                        'check_in_date' => $reservation->getCheckInDate(),
                        'check_out_date' => $reservation->getCheckOutDate(),
                        'total_price' => $reservation->getTotalPrice()
                    ]
                );
                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error("Error cancelling reservation", [
                'reservation_id' => $reservationId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Obtiene reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getReservationsByUser(int $userId): array
    {
        return $this->reservationRepository->findByUserId($userId);
    }

    /**
     * Obtiene reservas activas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getActiveReservationsByUser(int $userId): array
    {
        return $this->reservationRepository->findActiveByUserId($userId);
    }

    /**
     * Obtiene una reserva por ID
     *
     * @param int $reservationId
     * @param int|null $userId
     * @return Reservation|null
     */
    public function getReservationById(int $reservationId, ?int $userId = null): ?Reservation
    {
        $reservation = $this->reservationRepository->find($reservationId);
        
        if (!$reservation) {
            return null;
        }

        // Si se especifica userId, verificar que la reserva pertenece al usuario
        if ($userId !== null && $reservation->getUserId() !== $userId) {
            return null;
        }

        return $reservation;
    }

    /**
     * Verifica disponibilidad de un alojamiento
     *
     * @param int $accommodationId
     * @param string $checkIn
     * @param string $checkOut
     * @return bool
     */
    public function isAccommodationAvailable(int $accommodationId, string $checkIn, string $checkOut): bool
    {
        return !$this->reservationRepository->hasDateConflict($accommodationId, $checkIn, $checkOut);
    }

    /**
     * Calcula el precio total de una reserva
     *
     * @param int $accommodationId
     * @param string $checkIn
     * @param string $checkOut
     * @param int $guests
     * @return float
     */
    public function calculateTotalPrice(int $accommodationId, string $checkIn, string $checkOut, int $guests = 1): float
    {
        try {
            $accommodation = $this->accommodationRepository->find($accommodationId);
            
            if (!$accommodation) {
                return 0.0;
            }

            $checkInDate = new \DateTime($checkIn);
            $checkOutDate = new \DateTime($checkOut);
            $nights = $checkInDate->diff($checkOutDate)->days;

            $basePrice = $accommodation->getPrice();
            $totalPrice = $basePrice * $nights;

            // Aplicar lógica de precios por huéspedes si es necesario
            // Por ahora, el precio es por noche independientemente del número de huéspedes
            // En el futuro se podría implementar lógica más compleja

            return round($totalPrice, 2);
        } catch (Exception $e) {
            $this->logger->error("Error calculating total price", [
                'accommodation_id' => $accommodationId,
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return 0.0;
        }
    }

    /**
     * Valida los datos de una reserva
     *
     * @param array $data
     * @return array
     */
    public function validateReservationData(array $data): array
    {
        $errors = [];

        // Validar campos requeridos
        $requiredFields = ['user_id', 'accommodation_id', 'check_in_date', 'check_out_date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "El campo {$field} es requerido";
            }
        }

        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }

        // Validar fechas
        try {
            $checkIn = new \DateTime($data['check_in_date']);
            $checkOut = new \DateTime($data['check_out_date']);
            $today = new \DateTime();

            if ($checkIn < $today) {
                $errors[] = "La fecha de entrada no puede ser anterior a hoy";
            }

            if ($checkOut <= $checkIn) {
                $errors[] = "La fecha de salida debe ser posterior a la fecha de entrada";
            }

            // Validar que no sea más de 1 año en el futuro
            $maxDate = (new \DateTime())->add(new \DateInterval('P1Y'));
            if ($checkIn > $maxDate) {
                $errors[] = "No se pueden hacer reservas con más de 1 año de anticipación";
            }

        } catch (Exception $e) {
            $errors[] = "Formato de fechas inválido";
        }

        // Validar número de huéspedes
        $guests = $data['guests'] ?? 1;
        if (!is_numeric($guests) || $guests < 1 || $guests > 20) {
            $errors[] = "El número de huéspedes debe estar entre 1 y 20";
        }

        // Validar que el alojamiento existe
        if (isset($data['accommodation_id'])) {
            $accommodation = $this->accommodationRepository->find($data['accommodation_id']);
            if (!$accommodation) {
                $errors[] = "El alojamiento especificado no existe";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Obtiene estadísticas de reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getReservationStats(int $userId): array
    {
        return $this->reservationRepository->getStatsByUserId($userId);
    }

}
