<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'code',
        'free_access_days',
        'max_employees',
        'valid_from',
        'valid_to',
        'status',
        'notes',
    ];

    protected $casts = [
        'free_access_days' => 'integer',
        'max_employees' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function domains()
    {
        return $this->hasMany(CompanyDomain::class);
    }

    public function employees()
    {
        return $this->hasMany(CompanyEmployee::class);
    }

    public function employeeAccesses()
    {
        return $this->hasMany(CompanyEmployeeAccess::class);
    }

    public function activeEmployeeAccesses()
    {
        return $this->employeeAccesses()
            ->where('status', 'active')
            ->where('access_ends_at', '>=', now());
    }

    public function isActiveNow()
    {
        $today = now()->toDateString();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->valid_from && $today < $this->valid_from->toDateString()) {
            return false;
        }

        if ($this->valid_to && $today > $this->valid_to->toDateString()) {
            return false;
        }

        return true;
    }
}
