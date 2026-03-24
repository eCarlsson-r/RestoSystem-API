<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    const POINT_RATIO = 1000; // 1 Point for every Rp 1,000 spent

    protected $fillable = [
        'name', 'gender', 'pob', 'dob', 'address', 'points',
        'mobile', 'email', 'discount', 'tax', 'user_id'
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'is_birthday_today' => 'boolean',
        'dob' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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

    // Helper to calculate value
    public function getPointsValueAttribute() {
        return $this->points * 1; // Assuming 1 point = Rp 1
    }

    public function getTierAttribute() {
        if ($this->points >= 5000) return 'DIAMOND';
        if ($this->points >= 2000) return 'GOLD';
        if ($this->points >= 500) return 'SILVER';
        return 'BRONZE';
    }
}
