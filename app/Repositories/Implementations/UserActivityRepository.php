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
        $stmt = $this->db->prepare("SELECT * FROM user_activities WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ? $this->mapToModel($stmt->fetch()) : null;
    }

    /**
     * @inheritDoc
     */
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM user_activities");
        return array_map([$this, 'mapToModel'], $stmt->fetchAll());
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
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        $results = $stmt->fetchAll();

        return array_map([$this, 'mapToModel'], $results);
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
