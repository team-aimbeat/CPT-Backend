<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
   
   protected $fillable = [ 'user_id', 'package_id', 'referral_code_id', 'referral_referrer_id', 'total_amount', 'referral_credit_used', 'payment_type', 'txn_id', 'gateway_subscription_id', 'autopay_status', 'autopay_cancelled_at', 'trial_start_at', 'trial_ends_at', 'billing_starts_at', 'mandate_authorized_at', 'last_payment_failed_at', 'failure_reason', 'transaction_detail', 'payment_status', 'subscription_start_date', 'subscription_end_date', 'package_data', 'status', 'callback' ];

    protected $casts = [
        'user_id'  => 'integer',
        'package_id'  => 'integer',
        'total_amount' => 'double',
        'referral_credit_used' => 'double',
        'autopay_cancelled_at' => 'datetime',
        'trial_start_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'billing_starts_at' => 'datetime',
        'mandate_authorized_at' => 'datetime',
        'last_payment_failed_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function getPackageDataAttribute($value)
    {
        return isset($value) ? json_decode($value, true) : null;
    }

    public function setPackageDataAttribute($value)
    {
        $this->attributes['package_data'] = isset($value) ? json_encode($value) : null;
    }

    public function getTransactionDetailAttribute($value)
    {
        return isset($value) ? json_decode($value, true) : null;
    }

    public function setTransactionDetailAttribute($value)
    {
        $this->attributes['transaction_detail'] = isset($value) ? json_encode($value) : null;
    }

    public function scopeMySubscription($query, $user_id =null)
    {
        $user = auth()->user();

        if($user->hasRole(['user'])) {
            $query = $query->where('user_id', $user->id);
        }
        return $query;
    }
}
