<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'branch_id', 'table_id', 'employee_id', 'customer_id', 
        'date', 'time', 'discount', 'tax', 'status'
    ];

    public function invoices()
    {
        return $this->hasMany(SaleInvoice::class, 'sale_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function records()
    {
        return $this->hasMany(SaleRecord::class, 'sale_id');
    }
}
