<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleRecord extends Model
{
    protected $table = 'sale_records';

    protected $fillable = [
        'sale_id', 'item_type', 'item_code', 'item_price', 'quantity',
        'discount_percent', 'discount_amount', 'item_note', 
        'item_status', 'order_employee', 'order_date', 
        'order_time', 'deliver_employee'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_code');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'item_code');
    }
}
