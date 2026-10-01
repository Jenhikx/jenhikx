<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FounderWithdrawal extends Model
{
    protected $fillable = ['date', 'founder', 'amount', 'note', 'created_by'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}