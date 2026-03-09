<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepareRecipe extends Model
{
    protected $fillable = [
        'prepare_id', 'ingredient_id', 'qty', 'purchase_price'
    ];

    public function prepare()
    {
        return $this->belongsTo(Prepare::class, 'prepare_id', 'code');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'code');
    }
}
