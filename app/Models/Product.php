<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'desc', 'img_no', 
        'category_id', 'price', 'cost', 
        'discount', 'soldout'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'code');
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'product_id', 'code');
    }
}
