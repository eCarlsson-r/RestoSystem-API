<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'branch_id', 'table_number', 'floor_number', 'employee_id', 
        'customer_id', 'date', 'time', 'discount', 'tax', 'status'
    ];

    public function invoice()
    {
        return $this->hasOne(SaleInvoice::class, 'sale_id');
    }
}
