<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleInvoice extends Model
{
    protected $table = 'sale_invoices';

    protected $fillable = [
        'sale_id', 'paymethod', 'paybank', 
        'paycard', 'payamount', 'paychange', 
        'cardtype', 'voucher', 'employee_id'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}
