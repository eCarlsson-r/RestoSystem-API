<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'supplier_id', 'date', 'delivery_date', 
        'desc', 'tax'
    ];

    public function records()
    {
        return $this->hasMany(PurchaseReturnRecord::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
