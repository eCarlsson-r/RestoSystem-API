<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenRequest extends Model
{
    protected $fillable = [
        'date', 'time', 'from_branch_id', 'from_storage', 
        'to_branch_id', 'to_storage', 'respond_date', 
        'respond_time', 'status'
    ];

    public function items()
    {
        return $this->hasMany(KitchenRequestItem::class, 'request_id');
    }
}
