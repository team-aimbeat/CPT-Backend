<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'access_days',
        'max_redemptions',
        'per_user_limit',
        'valid_from',
        'valid_to',
        'first_purchase_only',
        'is_auto_generated',
        'source_subscription_id',
        'status',
        'description',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'first_purchase_only' => 'boolean',
        'is_auto_generated' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }
}
