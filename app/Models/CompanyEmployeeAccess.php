<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyEmployeeAccess extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'email',
        'access_starts_at',
        'access_ends_at',
        'verified_at',
        'source',
        'status',
        'metadata',
    ];

    protected $casts = [
        'access_starts_at' => 'datetime',
        'access_ends_at' => 'datetime',
        'verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->access_ends_at && now()->lt($this->access_ends_at);
    }
}
