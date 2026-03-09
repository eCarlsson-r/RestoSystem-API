<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenRequestItem extends Model
{
    protected $fillable = [
        'request_id', 'item_type', 'item_code', 'qty'
    ];

    public function request()
    {
        return $this->belongsTo(KitchenRequest::class);
    }
}
