<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPolicyAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'setting_id',
        'policy_type',
        'policy_title',
        'policy_content_hash',
        'policy_content',
        'accepted_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
