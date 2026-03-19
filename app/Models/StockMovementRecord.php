<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovementRecord extends Model
{
    protected $fillable = [
        'movement_id', 'item_type', 'item_code', 'quantity'
    ];

    public function movement()
    {
        return $this->belongsTo(StockMovement::class, 'movement_id');
    }
}
