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
}
