<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prepare extends Model
{
    protected $fillable = [
        'name', 'cost', 'quantity', 'unit'
    ];

    public function recipes()
    {
        return $this->hasMany(PrepareRecipe::class);
    }
}
