<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'item_type', 'item_code', 'branch_id', 
        'storage', 'purchase_price', 'quantity'
    ];

    public function logs()
    {
        return $this->hasMany(StockLog::class);
    }
}
