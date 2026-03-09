<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderRecord extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_type', 'item_code', 
        'qty', 'price', 'discount'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
