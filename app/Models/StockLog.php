<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = [
        'stock_id', 'invoice_id', 'desc', 
        'add_qty', 'get_qty', 'date', 'time'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
