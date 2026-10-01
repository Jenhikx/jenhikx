<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['name', 'product_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function adSpends()
    {
        return $this->hasMany(AdSpend::class);
    }

    public function getLabelAttribute()
    {
        return $this->name . ' - ' . $this->product_name;
    }

        public function pnlRates()
    {
        return $this->hasMany(PnlRate::class)->orderBy('effective_from');
    }
}