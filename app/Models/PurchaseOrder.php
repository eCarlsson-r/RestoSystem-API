<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id', 'date', 'delivery_date', 
        'description', 'tax', 'status'
    ];

    public function records()
    {
        return $this->hasMany(PurchaseOrderRecord::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
