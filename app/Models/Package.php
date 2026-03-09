<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'price', 'desc', 'img_no'
    ];

    public function products()
    {
        return $this->hasMany(PackageProduct::class, 'package_id', 'code');
    }
}
