<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrencyRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'currency_name',
        'currency_code',
        'exchange_rate',
        'status',
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'status' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }
}
