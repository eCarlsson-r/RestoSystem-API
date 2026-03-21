<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuffetItem extends Model
{
    protected $fillable = [
        'buffet_id',
        'product_id',
    ];

    public function buffet()
    {
        return $this->belongsTo(Buffet::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
