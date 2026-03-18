<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'item_type', 'item_code', 'branch_id', 
        'storage', 'purchase_price', 'quantity'
    ];

    protected $appends = ['item_name', 'unit'];

    public function getItemNameAttribute()
    {
        if ($this->item_type == 'INGR') {
            return $this->ingredient ? $this->ingredient->name : null;
        } elseif ($this->item_type == 'UTLT') {
            return $this->utility ? $this->utility->name : null;
        } elseif ($this->item_type == 'PREP') {
            return $this->prepare ? $this->prepare->name : null;
        }
        return 'Unknown';
    }

    public function getUnitAttribute()
    {
        if ($this->item_type == 'INGR') {
            return $this->ingredient ? $this->ingredient->unit : null;
        } elseif ($this->item_type == 'UTLT') {
            return $this->utility ? $this->utility->unit : null;
        } elseif ($this->item_type == 'PREP') {
            return $this->prepare ? $this->prepare->unit : null;
        }
        return null;
    }

    public function logs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'item_code');
    }

    public function utility()
    {
        return $this->belongsTo(Utility::class, 'item_code');
    }

    public function prepare()
    {
        return $this->belongsTo(Prepare::class, 'item_code');
    }
}
