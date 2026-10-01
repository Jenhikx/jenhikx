<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSpend extends Model
{
    protected $fillable = ['store_id', 'date', 'orders', 'ad_cost', 'created_by'];

    protected $casts = [
        'date' => 'date',
        'ad_cost' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getCpaAttribute()
    {
        return $this->orders > 0 ? $this->ad_cost / $this->orders : 0;
    }
}