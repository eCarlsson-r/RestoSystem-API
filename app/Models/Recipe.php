<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'product_id', 'ingredient_id', 'qty', 'purchase_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'code');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'code');
    }
}
