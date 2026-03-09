<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleRecord extends Model
{
    protected $table = 'sales-record';

    protected $fillable = [
        'sale_id', 'item_type', 'item_code', 'item_price', 
        'discount_pcnt', 'discount_amnt', 'item_note', 
        'item_status', 'order_employee', 'order_date', 
        'order_time', 'deliver_employee'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }
}
