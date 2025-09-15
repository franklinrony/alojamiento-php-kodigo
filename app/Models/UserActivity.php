<?php

namespace App\Models;

use App\Models\Model;

class UserActivity extends Model 
{
    /**
     * @var string
     */
    protected static $table = 'user_activities';

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string|null
     */
    public $details;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return static::$table;
    }

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type', 
        'details'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at'
    ];

    /**
     * Get the user that owns this activity
     * @return User|null
     */
    public function user()
    {
        $user = new User();
        $data = $this->toArray();
        $user->fill(['id' => $data['user_id']]);
        return $user;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return (int) $this->user_id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type ?? 'unknown';
    }

    /**
     * @return string|null
     */
    public function getDetails(): ?string
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}
