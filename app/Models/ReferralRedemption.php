<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralRedemption extends Model
{
    protected $fillable = [
        'referral_code_id',
        'referrer_id',
        'referred_user_id',
        'subscription_id',
        'reward_amount',
        'redeemed_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'reward_amount' => 'decimal:2',
    ];

    public function referralCode()
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
