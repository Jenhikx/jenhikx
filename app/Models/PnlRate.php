<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PnlRate extends Model
{
    protected $fillable = [
        'store_id',
        'effective_from',
        'delivery_percent',
        'margin',
        'rto_charge',
        'delivered_charge',
        'gst_percent',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'delivery_percent' => 'float',
        'margin' => 'float',
        'rto_charge' => 'float',
        'delivered_charge' => 'float',
        'gst_percent' => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}