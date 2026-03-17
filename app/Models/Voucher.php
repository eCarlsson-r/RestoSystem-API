<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'value',
        'status',
        'min_order_amount',
        'valid_until'
    ];
}
