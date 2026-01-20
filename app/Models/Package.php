<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Package extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    protected $fillable=[ 'name', 'duration_unit', 'duration', 'price', 'description', 'status', 'package_type' ];
    
    protected $casts = [
        'duration'      => 'integer',
        'price'         => 'double',
    ];

}
