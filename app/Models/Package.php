<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name', 'price', 'description'
    ];

    public function products()
    {
        return $this->hasMany(PackageProduct::class);
    }
}
