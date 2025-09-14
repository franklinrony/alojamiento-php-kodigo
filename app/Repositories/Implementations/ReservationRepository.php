<?php

namespace App\Repositories\Implementations;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Accommodation;
use App\Repositories\IReservationRepository;
use App\Repositories\Implementations\BaseRepository;
use PDO;
use PDOException;

/**
 * Class ReservationRepository
 * Implementación del repositorio de reservas
 */
class ReservationRepository extends BaseRepository implements IReservationRepository
{
    /**
     * @var string
     */
    protected $table = 'reservations';

    /**
     * @var string
     */
    protected $modelClass = Reservation::class;

    /**
     * Busca reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                       a.name as accommodation_name, a.location as accommodation_location
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN accommodations a ON r.accommodation_id = a.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC
            ");
            
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            
            return $this->hydrateModels($results);
        } catch (PDOException $e) {
            $this->logDatabaseError("Error finding reservations by user ID", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Busca reservas por alojamiento
     *
     * @param int $accommodationId
     * @return array
     */
    public function findByAccommodationId(int $accommodationId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                       a.name as accommodation_name, a.location as accommodation_location
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN accommodations a ON r.accommodation_id = a.id
                WHERE r.accommodation_id = ?
                ORDER BY r.created_at DESC
            ");
            
            $stmt->execute([$accommodationId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->hydrateModels($results);
        } catch (PDOException $e) {
            $this->logDatabaseError("Error finding reservations by accommodation ID", [
                'accommodation_id' => $accommodationId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Busca reservas activas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function findActiveByUserId(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                       a.name as accommodation_name, a.location as accommodation_location
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN accommodations a ON r.accommodation_id = a.id
                WHERE r.user_id = ? AND r.status = 'active'
                ORDER BY r.check_in_date ASC
            ");
            
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->hydrateModels($results);
        } catch (PDOException $e) {
            $this->logDatabaseError("Error finding active reservations by user ID", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Busca reservas por rango de fechas
     *
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $accommodationId
     * @return array
     */
    public function findByDateRange(string $checkIn, string $checkOut, ?int $accommodationId = null): array
    {
        try {
            $sql = "
                SELECT r.*, u.name as user_name, u.email as user_email,
                       a.name as accommodation_name, a.location as accommodation_location
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN accommodations a ON r.accommodation_id = a.id
                WHERE r.status = 'active' 
                AND (
                    (r.check_in_date <= ? AND r.check_out_date > ?) OR
                    (r.check_in_date < ? AND r.check_out_date >= ?) OR
                    (r.check_in_date >= ? AND r.check_out_date <= ?)
                )
            ";
            
            $params = [$checkOut, $checkIn, $checkOut, $checkIn, $checkIn, $checkOut];
            
            if ($accommodationId !== null) {
                $sql .= " AND r.accommodation_id = ?";
                $params[] = $accommodationId;
            }
            
            $sql .= " ORDER BY r.check_in_date ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->hydrateModels($results);
        } catch (PDOException $e) {
            $this->logDatabaseError("Error finding reservations by date range", [
                'start_date' => $checkIn,
                'end_date' => $checkOut,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Verifica si hay conflictos de fechas para un alojamiento
     *
     * @param int $accommodationId
     * @param string $checkIn
     * @param string $checkOut
     * @param int|null $excludeReservationId
     * @return bool
     */
    public function hasDateConflict(int $accommodationId, string $checkIn, string $checkOut, ?int $excludeReservationId = null): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE accommodation_id = ? 
                AND status = 'active'
                AND (
                    (check_in_date <= ? AND check_out_date > ?) OR
                    (check_in_date < ? AND check_out_date >= ?) OR
                    (check_in_date >= ? AND check_out_date <= ?)
                )
            ";
            
            $params = [$accommodationId, $checkOut, $checkIn, $checkOut, $checkIn, $checkIn, $checkOut];
            
            if ($excludeReservationId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excludeReservationId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['count'] > 0;
        } catch (PDOException $e) {
            $this->logDatabaseError("Error checking date conflict", [
                'accommodation_id' => $accommodationId,
                'start_date' => $checkIn,
                'end_date' => $checkOut,
                'error' => $e->getMessage()
            ]);
            return true; // En caso de error, asumir que hay conflicto por seguridad
        }
    }

    /**
     * Busca reservas por estado
     *
     * @param string $status
     * @return array
     */
    public function findByStatus(string $status): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                       a.name as accommodation_name, a.location as accommodation_location
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN accommodations a ON r.accommodation_id = a.id
                WHERE r.status = ?
                ORDER BY r.created_at DESC
            ");
            
            $stmt->execute([$status]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->hydrateModels($results);
        } catch (PDOException $e) {
            $this->logDatabaseError("Error finding reservations by status", [
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtiene estadísticas de reservas por usuario
     *
     * @param int $userId
     * @return array
     */
    public function getStatsByUserId(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_reservations,
                    COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) as active_reservations,
                    COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled_reservations,
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_reservations,
                    COALESCE(SUM(CASE WHEN status = 'active' THEN total_price ELSE 0 END), 0) as total_spent_active,
                    COALESCE(SUM(total_price), 0) as total_spent_all
                FROM {$this->table}
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total_reservations' => (int) $result['total_reservations'],
                'active_reservations' => (int) $result['active_reservations'],
                'cancelled_reservations' => (int) $result['cancelled_reservations'],
                'completed_reservations' => (int) $result['completed_reservations'],
                'total_spent_active' => (float) $result['total_spent_active'],
                'total_spent_all' => (float) $result['total_spent_all']
            ];
        } catch (PDOException $e) {
            $this->logDatabaseError("Error getting reservation stats by user ID", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'total_reservations' => 0,
                'active_reservations' => 0,
                'cancelled_reservations' => 0,
                'completed_reservations' => 0,
                'total_spent_active' => 0.0,
                'total_spent_all' => 0.0
            ];
        }
    }

    /**
     * Hidrata los resultados en modelos
     *
     * @param array $results
     * @return array
     */
    private function hydrateModels(array $results): array
    {
        $models = [];
        
        foreach ($results as $row) {
            $reservation = new Reservation();
            $reservation->fill($row);
            
            // Agregar información adicional si está disponible
            if (isset($row['user_name'])) {
                $user = new User();
                $user->setId($row['user_id']);
                $user->setName($row['user_name']);
                $user->setEmail($row['user_email']);
                $reservation->setUser($user);
            }
            
            if (isset($row['accommodation_name'])) {
                $accommodation = new Accommodation();
                $accommodation->setId($row['accommodation_id']);
                $accommodation->setName($row['accommodation_name']);
                $accommodation->setLocation($row['accommodation_location']);
                $reservation->setAccommodation($accommodation);
            }
            
            $models[] = $reservation;
        }
        
        return $models;
    }
}
