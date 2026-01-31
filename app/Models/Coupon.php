<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'status',
        'description',
    ];

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }
}
