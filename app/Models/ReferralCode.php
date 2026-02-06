<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'status',
        'max_redemptions',
    ];

    protected $casts = [
        'max_redemptions' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function redemptions()
    {
        return $this->hasMany(ReferralRedemption::class);
    }
}
