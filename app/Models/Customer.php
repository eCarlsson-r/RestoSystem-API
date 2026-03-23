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

    protected $casts = [
        'is_birthday_today' => 'boolean',
        'dob' => 'date',
    ];

    /**
     * Determine if today is the customer's birthday.
     */
    public function getIsBirthdayTodayAttribute(): bool
    {
        if (!$this->dob) return false;

        return $this->dob->isBirthday();
    }

    /**
     * Count only completed sales to define a "visit"
     */
    public function getTotalVisitsAttribute(): int
    {
        // We count sales that are 'Closed' (Status 'C') to ensure they actually ate and paid
        return $this->sales()->has('invoices')->count();
    }

    /**
     * Logic for the "Birthday Reward" eligibility.
     * Perhaps they only get a free buffet if they've visited at least 3 times before.
     */
    public function getCanClaimBirthdayBuffetAttribute(): bool
    {
        return $this->is_birthday_today && $this->total_visits >= 1;
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function reservations() {
        return $this->hasMany(Reservation::class);
    }

    // app/Models/Customer.php
    public function getTierAttribute() {
        if ($this->points >= 5000) return 'DIAMOND';
        if ($this->points >= 2000) return 'GOLD';
        if ($this->points >= 500) return 'SILVER';
        return 'BRONZE';
    }
}
