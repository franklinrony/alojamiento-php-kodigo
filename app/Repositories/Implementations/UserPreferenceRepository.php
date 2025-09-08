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
