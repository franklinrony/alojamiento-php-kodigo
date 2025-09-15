<?php

namespace App\Repositories\Implementations;

use App\Models\Accommodation;
use App\Repositories\IAccommodationRepository;

/**
 * Class AccommodationRepository
 * Implementación del repositorio de alojamientos
 */
class AccommodationRepository extends BaseRepository implements IAccommodationRepository
{
    /**
     * @param Accommodation $model
     */
    public function __construct(Accommodation $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByPriceRange(float $minPrice, float $maxPrice, ?int $limit = null, ?int $offset = null)
    {
        $sql = "SELECT * FROM accommodations WHERE price BETWEEN :min_price AND :max_price";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':min_price', $minPrice, \PDO::PARAM_STR);
            $stmt->bindValue(':max_price', $maxPrice, \PDO::PARAM_STR);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            
            return array_map([$this, 'mapToModel'], $stmt->fetchAll());
        } catch (\PDOException $e) {
            $this->logDatabaseError("Error en consulta findByPriceRange()", [
                'sql' => $sql,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'limit' => $limit,
                'offset' => $offset,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function findByLocation(string $location, ?int $limit = null, ?int $offset = null)
    {
        $sql = "SELECT * FROM accommodations WHERE location LIKE :location";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':location', "%$location%", \PDO::PARAM_STR);
            
            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            
            return array_map([$this, 'mapToModel'], $stmt->fetchAll());
        } catch (\PDOException $e) {
            $this->logDatabaseError("Error en consulta findByLocation()", [
                'sql' => $sql,
                'location' => $location,
                'limit' => $limit,
                'offset' => $offset,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function findByCreator(int $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM accommodations WHERE created_by = :user_id");
        $stmt->execute(['user_id' => $userId]);
        
        return array_map([$this, 'mapToModel'], $stmt->fetchAll());
    }

    /**
     * Cuenta alojamientos por rango de precios
     */
    public function countByPriceRange(float $minPrice, float $maxPrice): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM accommodations WHERE price BETWEEN :min_price AND :max_price");
        $stmt->execute([
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cuenta alojamientos por ubicación
     */
    public function countByLocation(string $location): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM accommodations WHERE location LIKE :location");
        $stmt->execute(['location' => "%$location%"]);
        
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cuenta todos los alojamientos
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM accommodations");
        return (int) $stmt->fetchColumn();
    }
}
