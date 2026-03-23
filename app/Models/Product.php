<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'category_id', 'price', 'cost', 'discount'];

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

    public function branches()
    {
        return $this->belongsToMany(Branch::class)->withPivot('is_active')->withTimestamps();
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
