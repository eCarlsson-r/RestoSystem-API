<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepareLog extends Model
{
    protected $fillable = [
        'prepare_id', 'branch_id', 'storage', 'date', 'time'
    ];

    public function prepare()
    {
        return $this->belongsTo(Prepare::class, 'prepare_id', 'code');
    }
}
