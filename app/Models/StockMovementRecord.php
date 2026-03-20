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

    public function ingredient()
    {
        if ($this->item_type == 'INGR') return $this->belongsTo(Ingredient::class, 'item_code');
    }

    public function utility()
    {
        if ($this->item_type == 'UTLT') return $this->belongsTo(Utility::class, 'item_code');
    }
}
