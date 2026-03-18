<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 
        'floor_number', 'kitchen_no', 'bartender_no'
    ];

    protected $guarded = ['id'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function kitchens()
    {
        return $this->hasMany(Kitchen::class);
    }

    public function bar()
    {
        return $this->hasMany(Bar::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }
}
