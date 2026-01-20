<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Level extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [ 'title','rate','level_image','status' ];

    protected $casts = [
        'rate'      => 'integer',
    ];
    
     protected $appends = ['level_image_url'];
     
       public function getLevelImageUrlAttribute()
    {
        if ($this->level_image) {
            return asset('https://fitness.completepersonaltraining.com/storage/' . $this->level_image);
        }
        return null;
    }
}
