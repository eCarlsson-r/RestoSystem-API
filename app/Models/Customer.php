<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'gender', 'pob', 'dob', 'address', 
        'mobile', 'email', 'discount', 'tax', 'img_no', 'account_id'
    ];
}
