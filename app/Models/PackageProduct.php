<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageProduct extends Model
{
    protected $fillable = [
        'package_id', 'product_id', 'qty'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'code');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'code');
    }
}
