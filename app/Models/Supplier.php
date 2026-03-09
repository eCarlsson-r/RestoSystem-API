<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'branch_id', 'storage', 'contact_person', 
        'npwp', 'address', 'phone', 'mobile', 'email'
    ];
}
