<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSubscription extends Model
{
    protected $fillable = [
        'user_id', 'endpoint', 'public_key', 'auth_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
