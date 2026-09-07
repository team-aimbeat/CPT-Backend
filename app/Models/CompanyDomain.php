<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDomain extends Model
{
    protected $fillable = [
        'company_id',
        'domain',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
