<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'slug', 'address', 'city', 'phone', 
        'floor_number', 'kitchen_no', 'bartender_no', 'is_active'
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

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('is_active')->withTimestamps();
    }

    public function buffets() 
    {
        return $this->belongsToMany(Buffet::class, 'branch_buffet');
    }

    public function getTotalCapacityAttribute()
    {
        return $this->tables()->sum('capacity');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'model');
    }
}
