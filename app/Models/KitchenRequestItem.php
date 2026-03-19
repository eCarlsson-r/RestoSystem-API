<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenRequestItem extends Model
{
    protected $fillable = [
        'request_id', 'item_type', 'item_code', 'quantity'
    ];

    public function request()
    {
        return $this->belongsTo(KitchenRequest::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'item_code');
    }

    public function utility()
    {
        return $this->belongsTo(Utility::class, 'item_code');
    }
}
