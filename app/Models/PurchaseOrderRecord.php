<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderRecord extends Model
{
    protected $fillable = [
        'purchase_order_id', 'item_type', 'item_code', 
        'quantity', 'price', 'discount'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
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
