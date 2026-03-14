<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnRecord extends Model
{
    protected $fillable = [
        'purchase_return_id', 'item_type', 'item_code', 
        'quantity', 'price', 'discount'
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }
}
