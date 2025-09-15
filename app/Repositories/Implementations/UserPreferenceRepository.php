<?php

namespace App\Repositories\Implementations;

use App\Repositories\IUserPreferenceRepository;
use App\Models\UserPreference;

class UserPreferenceRepository extends BaseRepository implements IUserPreferenceRepository
{
    public function __construct(UserPreference $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function all(?int $limit = null, ?int $offset = null)
    {
        $sql = "SELECT * FROM user_preferences";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return array_map([$this, 'mapToModel'], $stmt->fetchAll());
    }

    /**
     * @inheritDoc
     */
    public function getUserPreferences(int $userId): ?array
    {
        $table = $this->getTableName();
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function updatePreferences(int $userId, array $preferences): bool
    {
        $current = $this->getUserPreferences($userId);
        $table = $this->getTableName();
        
        if (!$current) {
            $preferences['user_id'] = $userId;
            return $this->create($preferences) !== null;
        }

        $sets = [];
        $params = ['user_id' => $userId];
        
        foreach ($preferences as $key => $value) {
            if ($key === 'user_id') continue;
            $sets[] = "$key = :$key";
            $params[$key] = $value;
        }

        if (empty($sets)) {
            return true;
        }

        $setClause = implode(', ', $sets);
        $sql = "UPDATE $table SET $setClause WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
