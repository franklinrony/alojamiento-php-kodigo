<?php

namespace App\Models;

use App\Models\Model;

class UserPreference extends Model 
{
    /**
     * @var string
     */
    protected static $table = 'user_preferences';

    /**
     * @var int
     */
    public $user_id;

    /**
     * @var string
     */
    public $notifications;

    /**
     * @var bool
     */
    public $email_updates;

    /**
     * @var string
     */
    public $created_at;

    /**
     * @var string
     */
    public $updated_at;

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
        'notifications',
        'email_updates'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'updated_at'
    ];

    /**
     * Get the user that owns these preferences
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
    public function getNotifications(): string
    {
        return $this->notifications;
    }

    /**
     * @return bool
     */
    public function getEmailUpdates(): bool
    {
        return (bool) $this->email_updates;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }
}
