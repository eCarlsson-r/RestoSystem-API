<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name', 'branch_id', 'gender', 'status', 'job_type', 
        'join_date', 'quit_date', 'home_address', 'phone', 
        'mobile', 'email', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
