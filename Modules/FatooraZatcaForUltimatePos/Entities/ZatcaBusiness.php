<?php

namespace Modules\FatooraZatcaForUltimatePos\Entities;

use App\Business;

class ZatcaBusiness extends Business
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'zatca_fields',
        'zatca_settings',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'zatca_fields' => 'array',
        'zatca_settings' => 'array',
    ];

    public function getZatcaVerifiedAttribute()
    {
        return ! empty($this->zatca_settings);
    }

    public function getZatcaField($key)
    {
        return $this->zatca_fields[$key] ?? null;
    }

    public function setZatcaFields($array)
    {
        $this->zatca_fields = array_merge($this->zatca_fields, $array);
    }
}
