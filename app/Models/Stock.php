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

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'item_code');
    }

    public function utility()
    {
        return $this->belongsTo(Utility::class, 'item_code');
    }
}
