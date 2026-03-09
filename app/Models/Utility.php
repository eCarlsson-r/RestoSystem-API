<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utility extends Model
{
    protected $fillable = [
        'name', 'desc', 'unit', 'img_no'
    ];
}
