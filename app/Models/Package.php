<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Package extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable=[ 'name', 'duration_unit', 'duration', 'price', 'description', 'status', 'package_type', 'offer_enabled', 'offer_type', 'offer_access_days', 'offer_max_redemptions' ];
    
    protected $casts = [
        'duration'      => 'integer',
        'price'         => 'double',
        'offer_enabled' => 'boolean',
        'offer_access_days' => 'integer',
        'offer_max_redemptions' => 'integer',
    ];

}
