<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $fillable = [
        'stock_id', 'invoice_id', 'description', 'add_qty', 'get_qty'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
