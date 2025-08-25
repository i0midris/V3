<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';

    protected $fillable = [
        'business_id',
        'country',
        'currency',
        'code',
        'symbol',
        'thousand_separator',
        'decimal_separator',
        'exchange_rate',
        'status',
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'status' => 'boolean',
    ];

    public $timestamps = true;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getNameAttribute()
    {
        return "{$this->code} - {$this->currency}";
    }
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }
}
