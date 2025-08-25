<?php

namespace Modules\Superadmin\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminCoupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
