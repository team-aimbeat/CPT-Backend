<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'package_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_signature',
        'amount',
        'currency',
        'status',
        'method',
        'invoice_path'
    ];

    // User ke saath relationship (Kis user ne pay kiya)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Subscription ke saath relationship (Kis subscription ke liye hai)
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Package ke saath relationship (Kaunsa package kharida gaya)
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}