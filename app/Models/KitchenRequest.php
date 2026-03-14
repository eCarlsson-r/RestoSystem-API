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

    public function from_branch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function to_branch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
}
