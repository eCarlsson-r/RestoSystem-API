<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoice extends Model
{
    protected $table = 'sale_invoices';

    protected $fillable = [
        'sale_id', 'pay_method', 'pay_bank', 
        'pay_card', 'pay_amount', 'pay_change', 
        'card_type', 'voucher', 'employee_id'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
