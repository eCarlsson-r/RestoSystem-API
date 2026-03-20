<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'product_id', 'item_type', 'item_code', 'quantity', 'unit', 'purchase_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function item()
    {
        if ($this->item_type === 'PREP') {
            return $this->belongsTo(Prepare::class, 'item_code');
        }
        return $this->belongsTo(Ingredient::class, 'item_code');
    }

    public function prepare()
    {
        if ($this->item_type == 'PREP') return $this->belongsTo(Prepare::class, 'item_code');
    }

    public function ingredient()
    {
        if ($this->item_type == 'INGR') return $this->belongsTo(Ingredient::class, 'item_code');
    }
}
