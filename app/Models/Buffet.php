<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buffet extends Model
{
    protected $fillable = [
        'name', 'price_adult', 'price_child', 'duration_minutes', 'is_active', 'description'
    ];

    protected $guarded = ['id'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'buffet_items');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }

    public function items()
    {
        return $this->hasMany(BuffetItem::class);
    }
}
