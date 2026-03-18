<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'category_id', 'price', 'cost', 'discount', 'soldout'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function recipe()
    {
        return $this->hasMany(Recipe::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }
}
