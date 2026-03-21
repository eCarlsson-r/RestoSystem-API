<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'employee_id', 'event_date', 'event_time',
        'buffet_id', 'branch_id', 'guaranteed_pax',
        'deposit_amount', 'deposit_status', 'status', 'notes', 'sale_id'
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
        'deposit_amount' => 'decimal:2',
    ];

    // Relationships
    public function buffet() {
        return $this->belongsTo(Buffet::class, 'buffet_id');
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    // Scopes for the Admin Dashboard
    public function scopeUpcoming($query) {
        return $query->where('event_date', '>=', now()->toDateString())
                     ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeToday($query) {
        return $query->where('event_date', now()->toDateString());
    }
}