<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = [
        'table_number', 'floor_number', 'branch_id', 'capacity', 'size', 
        'direction', 'status', 'position_x', 'position_y', 'shape'
    ];

    protected $guarded = ['id'];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'table_id')->where('status', '<>', 'D')->where('status', '<>', 'X');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
