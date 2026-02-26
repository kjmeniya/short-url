<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification as LaravelDatabaseNotification;

class Notification extends LaravelDatabaseNotification
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'data',
        'read_at',
        'deleted_at',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who deleted the notification.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }
}
