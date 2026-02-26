<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'user_id',
        'guest_id',
        'page',
        'platform',
        'device',
        'ip',
        'visited_at'
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
