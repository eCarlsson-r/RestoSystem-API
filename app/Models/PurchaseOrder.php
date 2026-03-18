<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id', 'branch_id', 'storage', 'date', 
        'delivery_date', 'description', 'tax', 'status'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderRecord::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
