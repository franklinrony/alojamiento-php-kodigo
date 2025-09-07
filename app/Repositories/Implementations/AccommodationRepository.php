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
    public function findByPriceRange(float $minPrice, float $maxPrice)
    {
        $sql = "SELECT * FROM accommodations WHERE price BETWEEN :min_price AND :max_price";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);
        
        return array_map([$this, 'mapToModel'], $stmt->fetchAll());
    }

    /**
     * @inheritDoc
     */
    public function findByLocation(string $location)
    {
        $stmt = $this->db->prepare("SELECT * FROM accommodations WHERE location LIKE :location");
        $stmt->execute(['location' => "%$location%"]);
        
        return array_map([$this, 'mapToModel'], $stmt->fetchAll());
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
}
