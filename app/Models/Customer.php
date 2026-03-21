<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'gender', 'pob', 'dob', 'address', 
        'mobile', 'email', 'discount', 'tax', 'account_id'
    ];

    protected $guarded = ['id'];

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function reservations() {
        return $this->hasMany(Reservation::class);
    }
}
