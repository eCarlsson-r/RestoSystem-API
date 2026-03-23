<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon_name', 'kitchen_process', 'description'
    ];

    protected $guarded = ['id'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }
}
