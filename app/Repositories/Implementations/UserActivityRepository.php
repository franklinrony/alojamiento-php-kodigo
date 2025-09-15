<?php

namespace App\Repositories\Implementations;

use App\Repositories\IUserActivityRepository;
use App\Models\UserActivity;
use App\Repositories\Implementations\BaseRepository;

class UserActivityRepository extends BaseRepository implements IUserActivityRepository
{
    public function __construct(UserActivity $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_activities WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            
            if ($result === false || $result === null) {
                return null;
            }
            
            return $this->mapToModel($result);
        } catch (\Exception $e) {
            // Log del error usando el sistema de logging
            if (class_exists('\App\Services\ILoggerService')) {
                try {
                    $container = \App\Utilities\DiContainer::getInstance();
                    if ($container->has(\App\Services\ILoggerService::class)) {
                        $logger = $container->get(\App\Services\ILoggerService::class);
                        $logger->error("Error en UserActivityRepository::find()", [
                            'error' => $e->getMessage(),
                            'id' => $id,
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                    }
                } catch (\Exception $logError) {
                    // Fallback silencioso
                }
            }
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function all(?int $limit = null, ?int $offset = null)
    {
        // Usar el método padre que ya maneja la lógica correctamente
        return parent::all($limit, $offset);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        $table = "user_activities";
        $fields = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO $table ($fields) VALUES ($values)");
        $stmt->execute($data);
        
        return $this->find($this->db->lastInsertId());
    }

    /**
     * @inheritDoc
     */
    public function update($id, array $data)
    {
        $table = "user_activities";
        $set = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($data)));
        $data['id'] = $id;
        
        $stmt = $this->db->prepare("UPDATE $table SET $set WHERE id = :id");
        return $stmt->execute($data);
    }

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM user_activities WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function getActivitiesByUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_activities WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return array_map([$this, 'mapToModel'], $stmt->fetchAll());
    }

    /**
     * @inheritDoc
     */
    public function logActivity(int $userId, string $type, ?string $details = null): bool
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'details' => $details
        ];

        return $this->create($data) !== null;
    }
}
