<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'from_branch_id', 'from_storage', 'to_branch_id', 
        'to_storage', 'date', 'time', 'status'
    ];

    public function records()
    {
        return $this->hasMany(StockMovementRecord::class, 'movement_id');
    }
}
